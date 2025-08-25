<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Priority;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskMedia;
use App\Models\ChMessage;
use App\Models\TaskBriefQuestion;
use App\Models\TaskBriefChecklist;
use App\Models\Portfolio;

class TaskController extends BaseController
{
    /**
     * Get all tasks with pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Task::with(['users', 'status', 'priority', 'taskType', 'project']);
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            }

            if ($request->has('priority_id')) {
                $query->where('priority_id', $request->priority_id);
            }

            if ($request->has('user_id')) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            }

            if ($request->has('client_id')) {
                $query->whereHas('clients', function ($q) use ($request) {
                    $q->where('client_id', $request->client_id);
                });
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tasks = $query->paginate($request->get('per_page', 15));

            // Check for expired tasks with strict deadlines
            $rejectedStatus = Status::where('title', 'Rejected')->first();
            if ($rejectedStatus) {
                foreach ($tasks->items() as $task) {
                    if ($task->end_date && $task->close_deadline == 1) {
                        $deadline = Carbon::parse($task->end_date);
                        $current_time = now();
                        
                        if ($current_time > $deadline && $task->status_id != $rejectedStatus->id) {
                            $task->update(['status_id' => $rejectedStatus->id]);
                        }
                    }
                }
            }

            return $this->sendPaginatedResponse($tasks, 'Tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving tasks: ' . $e->getMessage());
        }
    }

    /**
     * Store a new task
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status_id' => 'required|exists:statuses,id',
                'priority_id' => 'required|exists:priorities,id',
                'task_type_id' => 'nullable|exists:task_types,id',
                'project_id' => 'required|exists:projects,id',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'exists:users,id',
                'client_ids' => 'nullable|array',
                'client_ids.*' => 'exists:clients,id',
                'tag_ids' => 'nullable|array',
                'tag_ids.*' => 'exists:tags,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',

            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            // Get template data if template_id is provided
            $template = null;
            if ($request->has('template_id') && $request->template_id) {
                $template = \App\Models\TaskBriefTemplates::find($request->template_id);
            }

            $task = Task::create([
                'title' => $request->title,
                'description' => $template ? ($template->description ?: $request->description) : $request->description,
                'status_id' => $request->status_id,
                'priority_id' => $request->priority_id,
                'task_type_id' => $request->task_type_id,
                'project_id' => $request->project_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'note' => $template ? ($template->standard_brief ?: $request->note) : $request->note,
                'close_deadline' => $request->get('close_deadline', 0),

                'created_by' => $request->user()->id,
            ]);

            // Attach users
            if ($request->has('user_ids')) {
                $task->users()->attach($request->user_ids);
            }

            // Attach clients
            if ($request->has('client_ids')) {
                $task->clients()->attach($request->client_ids);
            }

            // Attach tags
            if ($request->has('tag_ids')) {
                $task->tags()->attach($request->tag_ids);
            }



            DB::commit();

            $task->load(['users', 'status', 'priority', 'taskType']);

            return $this->sendResponse($task, 'Task created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error creating task: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific task
     */
    public function show($id)
    {
        try {
            $task = Task::with(['users', 'status', 'priority', 'taskType', 'project', 'clients', 'questionAnswers.briefQuestions', 'checklistAnswers', 'deliverables.creator', 'deliverables.media', 'messages.sender'])
                ->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Check if task is expired and has strict deadline
            if ($task->end_date && $task->close_deadline == 1) {
                $deadline = Carbon::parse($task->end_date);
                $current_time = now();
                
                if ($current_time > $deadline) {
                    // Task is expired with strict deadline - mark as Rejected
                    $rejectedStatus = Status::where('title', 'Rejected')->first();
                    if ($rejectedStatus && $task->status_id != $rejectedStatus->id) {
                        $task->update(['status_id' => $rejectedStatus->id]);
                        $task->load(['users', 'status', 'priority', 'taskType', 'project', 'clients']);
                    }
                }
            }

            return $this->sendResponse($task, 'Task retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task: ' . $e->getMessage());
        }
    }

    /**
     * Update a task
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status_id' => 'sometimes|required|exists:statuses,id',
                'priority_id' => 'sometimes|required|exists:priorities,id',
                'task_type_id' => 'nullable|exists:task_types,id',
                'project_id' => 'sometimes|required|exists:projects,id',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'exists:users,id',
                'client_ids' => 'nullable|array',
                'client_ids.*' => 'exists:clients,id',
                'tag_ids' => 'nullable|array',
                'tag_ids.*' => 'exists:tags,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'close_deadline' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $task->update($request->only([
                'title', 'description', 'status_id', 'priority_id', 
                'task_type_id', 'project_id', 'start_date', 'end_date', 'close_deadline'
            ]));

            // Sync users
            if ($request->has('user_ids')) {
                $task->users()->sync($request->user_ids);
            }

            // Sync clients
            if ($request->has('client_ids')) {
                $task->clients()->sync($request->client_ids);
            }

            // Sync tags
            if ($request->has('tag_ids')) {
                $task->tags()->sync($request->tag_ids);
            }

            DB::commit();

            $task->load(['users', 'status', 'priority', 'taskType', 'project', 'clients']);

            return $this->sendResponse($task, 'Task updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error updating task: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task
     */
    public function destroy($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $task->delete();

            return $this->sendResponse(null, 'Task deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting task: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple tasks
     */
    public function destroyMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'task_ids' => 'required|array',
                'task_ids.*' => 'exists:tasks,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $tasks = Task::whereIn('id', $request->task_ids)->get();

            foreach ($tasks as $task) {
                $task->delete();
            }

            return $this->sendResponse(null, 'Tasks deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting tasks: ' . $e->getMessage());
        }
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status_id' => 'required|exists:statuses,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $task->update(['status_id' => $request->status_id]);

            return $this->sendResponse($task, 'Task status updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating task status: ' . $e->getMessage());
        }
    }

    /**
     * Update task deadline
     */
    public function updateDeadline(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'end_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $task = Task::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $task->update(['end_date' => $request->end_date]);

            return $this->sendResponse($task, 'Task deadline updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating task deadline: ' . $e->getMessage());
        }
    }

    /**
     * Get task information
     */
    public function getInformation($id)
    {
        try {
            $task = Task::with([
                'users', 'clients', 'status', 'priority', 'project', 'tags',
                'questionAnswers', 'checklistAnswers'
            ])
            ->where('workspace_id', Auth::user()->workspace_id)
            ->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            return $this->sendResponse($task, 'Task information retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task information: ' . $e->getMessage());
        }
    }



    /**
     * Upload media for a task
     */
    public function uploadMedia(Request $request, $id)
    {
        try {
            $task = Task::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $validator = Validator::make($request->all(), [
                'media' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            // Store the media file
            $mediaPath = $request->file('media')->store('task-media', 'public');
            
            // Create media record (assuming you have a TaskMedia model)
            $media = $task->media()->create([
                'file_path' => $mediaPath,
                'file_name' => $request->file('media')->getClientOriginalName(),
                'file_size' => $request->file('media')->getSize(),
                'mime_type' => $request->file('media')->getMimeType(),
                'description' => $request->description,
                'uploaded_by' => Auth::user()->id,
            ]);

            return $this->sendResponse($media, 'Media uploaded successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error uploading media: ' . $e->getMessage());
        }
    }

    /**
     * Get media for a task
     */
    public function getMedia($id)
    {
        try {
            $task = Task::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $media = $task->media()->orderBy('created_at', 'desc')->get();

            return $this->sendResponse($media, 'Task media retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task media: ' . $e->getMessage());
        }
    }

    /**
     * Delete media
     */
    public function deleteMedia($mediaId)
    {
        try {
            // Find media by ID and ensure it belongs to a task in the user's workspace
            $media = TaskMedia::whereHas('task', function ($query) {
                $query->where('workspace_id', Auth::user()->workspace_id);
            })->find($mediaId);

            if (!$media) {
                return $this->sendNotFound('Media not found');
            }

            // Delete file from storage
            if (Storage::exists($media->file_path)) {
                Storage::delete($media->file_path);
            }

            $media->delete();

            return $this->sendResponse(null, 'Media deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting media: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple media
     */
    public function deleteMultipleMedia(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'media_ids' => 'required|array',
                'media_ids.*' => 'exists:task_media,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $media = TaskMedia::whereIn('id', $request->media_ids)
                ->whereHas('task', function ($query) {
                    $query->where('workspace_id', Auth::user()->workspace_id);
                })
                ->get();

            foreach ($media as $item) {
                if (Storage::exists($item->file_path)) {
                    Storage::delete($item->file_path);
                }
                $item->delete();
            }

            return $this->sendResponse(null, 'Media deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting media: ' . $e->getMessage());
        }
    }

    /**
     * Upload message for a task
     */
    public function uploadMessage(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Check if task is past due with strict deadline
            if ($task->end_date && $task->close_deadline == 1) {
                $deadline = Carbon::parse($task->end_date);
                $current_time = now();
                
                if ($current_time > $deadline) {
                    return $this->sendError('Cannot add comments to a task that is past its strict deadline', [], 403);
                }
            }

            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
                'message_type' => 'sometimes|string|in:text,file,image',
                'file' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:5120',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $messageData = [
                'message_text' => $request->message,
                'sender_id' => Auth::user()->id,
            ];

            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('task-messages', 'public');
                $messageData['file_path'] = $filePath;
                $messageData['file_name'] = $request->file('file')->getClientOriginalName();
            }

            $message = $task->messages()->create($messageData);

            return $this->sendResponse($message, 'Message uploaded successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error uploading message: ' . $e->getMessage());
        }
    }

    /**
     * Get messages for a task
     */
    public function getMessages($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $messages = $task->messages()
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $this->sendPaginatedResponse($messages, 'Task messages retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task messages: ' . $e->getMessage());
        }
    }

    /**
     * Delete message
     */
    public function deleteMessage($messageId)
    {
        try {
            $message = ChMessage::find($messageId);

            if (!$message) {
                return $this->sendNotFound('Message not found');
            }

            // Only allow user to delete their own messages or admin
            if ($message->sender_id !== Auth::user()->id && !Auth::user()->hasRole('admin')) {
                return $this->sendForbidden('You can only delete your own messages');
            }

            // Delete file if exists
            if ($message->file_path && Storage::exists($message->file_path)) {
                Storage::delete($message->file_path);
            }

            $message->delete();

            return $this->sendResponse(null, 'Message deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting message: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple messages
     */
    public function deleteMultipleMessages(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message_ids' => 'required|array',
                'message_ids.*' => 'exists:task_messages,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $messages = TaskMessage::whereIn('id', $request->message_ids)
                ->whereHas('task', function ($query) {
                    $query->where('workspace_id', Auth::user()->workspace_id);
                })
                ->get();

            foreach ($messages as $message) {
                // Only allow user to delete their own messages or admin
                if ($message->sent_by !== Auth::user()->id && !Auth::user()->hasRole('admin')) {
                    continue;
                }

                if ($message->file_path && Storage::exists($message->file_path)) {
                    Storage::delete($message->file_path);
                }
                $message->delete();
            }

            return $this->sendResponse(null, 'Messages deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting messages: ' . $e->getMessage());
        }
    }

    /**
     * Submit question answer
     */
    public function submitQuestionAnswer(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Check if task is past due with strict deadline
            if ($task->end_date && $task->close_deadline == 1) {
                $deadline = Carbon::parse($task->end_date);
                $current_time = now();
                
                if ($current_time > $deadline) {
                    return $this->sendError('Cannot submit answers for a task that is past its strict deadline', [], 403);
                }
            }

            $validator = Validator::make($request->all(), [
                'question_id' => 'required|exists:task_brief_questions,id',
                'answer' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $answer = $task->questionAnswers()->updateOrCreate(
                [
                    'question_id' => $request->question_id,
                    'answer_by' => Auth::user()->id,
                ],
                [
                    'question_answer' => $request->answer,
                ]
            );

            return $this->sendResponse($answer, 'Question answer submitted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error submitting question answer: ' . $e->getMessage());
        }
    }

    /**
     * Submit checklist answer
     */
    public function submitChecklistAnswer(Request $request, $id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Check if task is past due with strict deadline
            if ($task->end_date && $task->close_deadline == 1) {
                $deadline = Carbon::parse($task->end_date);
                $current_time = now();
                
                if ($current_time > $deadline) {
                    return $this->sendError('Cannot submit checklist answers for a task that is past its strict deadline', [], 403);
                }
            }

            $validator = Validator::make($request->all(), [
                'checklist_id' => 'required|exists:task_brief_checklists,id',
                'completed' => 'required|boolean',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $answer = $task->checklistAnswers()->updateOrCreate(
                [
                    'checklist_id' => $request->checklist_id,
                    'answer_by' => Auth::user()->id,
                ],
                [
                    'checklist_answer' => [
                        'completed' => $request->completed,
                        'notes' => $request->notes,
                    ],
                ]
            );

            return $this->sendResponse($answer, 'Checklist answer submitted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error submitting checklist answer: ' . $e->getMessage());
        }
    }
}
