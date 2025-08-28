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
use App\Services\NotificationService;

class TaskController extends BaseController
{
    /**
     * Get all tasks with pagination
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Task::with(['users', 'status', 'priority', 'taskType', 'template', 'project', 'clients']);
            
            // Role-based filtering
            if ($user->hasRole('requester')) {
                // Requesters only see tasks they created
                $query->where('created_by', $user->id);
            } elseif ($user->hasRole('tasker')) {
                // Taskers only see tasks they're assigned to
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            }
            // Admins and sub-admins see all tasks (no additional filtering)

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

            // Apply role-based data protection to task users
            if (!$user->hasRole(['admin', 'sub_admin'])) {
                // Remove sensitive data from assigned users for requesters and taskers
                $tasks->getCollection()->transform(function ($task) {
                    if ($task->users) {
                        foreach ($task->users as $taskUser) {
                            unset($taskUser->email);
                            unset($taskUser->phone);
                        }
                    }
                    return $task;
                });
            }

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
                'is_repeating' => 'boolean',
                'repeat_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
                'repeat_interval' => 'nullable|integer|min:1',
                'repeat_until' => 'nullable|date|after:start_date',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            // Get template data if template_id is provided
            $template = null;
            $templateQuestions = null;
            $templateChecklist = null;
            
            if ($request->has('template_id') && $request->template_id) {
                $template = \App\Models\TaskBriefTemplates::find($request->template_id);
                
                if ($template) {
                    // Get template questions
                    $questions = \App\Models\TaskBriefQuestion::where('task_brief_templates_id', $template->id)->get();
                    $templateQuestions = $questions->map(function($question) {
                        return [
                            'id' => $question->id,
                            'question_text' => $question->question_text,
                            'question_type' => $question->question_type,
                            'options' => $question->options,
                        ];
                    })->toArray();
                    
                    // Get template checklist items
                    $checklists = \App\Models\TaskBriefChecklist::where('task_brief_templates_id', $template->id)->get();
                    $templateChecklist = $checklists->map(function($checklist) {
                        return [
                            'id' => $checklist->id,
                            'checklist' => $checklist->checklist,
                        ];
                    })->toArray();
                }
            }

            $task = Task::create([
                'title' => $template ? $template->title : $request->title,
                'description' => $template ? $template->standard_brief : $request->description,
                'status_id' => $request->status_id,
                'priority_id' => $request->priority_id,
                'task_type_id' => $request->task_type_id,
                'template_id' => $template ? $template->id : null,
                'project_id' => $request->project_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'note' => $template ? $template->description : $request->note,
                'deliverable_quantity' => $template ? $template->deliverable_quantity : $request->get('deliverable_quantity', 1),
                'close_deadline' => $request->get('close_deadline', 0),
                'is_repeating' => $request->get('is_repeating', false),
                'repeat_frequency' => $request->get('repeat_frequency'),
                'repeat_interval' => $request->get('repeat_interval', 1),
                'repeat_until' => $request->get('repeat_until'),
                'repeat_active' => $request->get('is_repeating', false),
                'created_by' => $request->user()->id,
                'template_questions' => $templateQuestions,
                'template_checklist' => $templateChecklist,
                'template_standard_brief' => $template ? $template->standard_brief : null,
                'template_description' => $template ? $template->description : null,
                'template_deliverable_quantity' => $template ? $template->deliverable_quantity : null,
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

            // Send notifications for task assignment
            if ($request->has('user_ids') && !empty($request->user_ids)) {
                $notificationService = new NotificationService();
                foreach ($request->user_ids as $userId) {
                    $assignedUser = User::find($userId);
                    if ($assignedUser) {
                        $notificationService->taskAssigned($task, $assignedUser);
                    }
                }
            }

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
            $user = Auth::user();
            $task = Task::with(['users', 'status', 'priority', 'taskType', 'template', 'project', 'clients', 'questionAnswers.briefQuestions', 'checklistAnswers', 'deliverables.creator', 'deliverables.media', 'messages.sender'])
                ->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Role-based access control
            if ($user->hasRole('requester')) {
                // Requesters can only access tasks they created
                if ($task->created_by !== $user->id) {
                    return $this->sendError('Access denied', [], 403);
                }
            } elseif ($user->hasRole('tasker')) {
                // Taskers can only access tasks they're assigned to
                $isAssigned = $task->users()->where('users.id', $user->id)->exists();
                if (!$isAssigned) {
                    return $this->sendError('Access denied', [], 403);
                }
            }
            // Admins and sub-admins can access all tasks

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

            // Apply role-based data protection to task users
            if (!$user->hasRole(['admin', 'sub_admin'])) {
                // Remove sensitive data from assigned users for requesters and taskers
                if ($task->users) {
                    foreach ($task->users as $taskUser) {
                        unset($taskUser->email);
                        unset($taskUser->phone);
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
            $user = Auth::user();
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Role-based access control
            if ($user->hasRole('requester')) {
                // Requesters can only update tasks they created
                if ($task->created_by !== $user->id) {
                    return $this->sendError('Access denied', [], 403);
                }
            } elseif ($user->hasRole('tasker')) {
                // Taskers can only update tasks they're assigned to
                $isAssigned = $task->users()->where('users.id', $user->id)->exists();
                if (!$isAssigned) {
                    return $this->sendError('Access denied', [], 403);
                }
            }
            // Admins and sub-admins can update all tasks

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

            $oldStatus = $task->status;
            $task->update(['status_id' => $request->status_id]);
            $task->load('status');

            // Check if task was completed
            $completedStatus = Status::where('title', 'Completed')->first();
            if ($completedStatus && $request->status_id == $completedStatus->id && $oldStatus->id != $completedStatus->id) {
                $notificationService = new NotificationService();
                $notificationService->taskCompleted($task, Auth::user());
            }

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

            $task = Task::find($id);

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
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Handle both single file and multiple files
            if ($request->hasFile('media')) {
                // Single file upload (existing functionality)
                $validator = Validator::make($request->all(), [
                    'media' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,mp4,mov|max:10240',
                    'description' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return $this->sendValidationError($validator->errors());
                }

                // Use Spatie Media Library to add the file
                $media = $task->addMedia($request->file('media'))
                    ->withCustomProperties(['description' => $request->description])
                    ->toMediaCollection('task-media', 'public');

                return $this->sendResponse($media, 'Media uploaded successfully');
            } elseif ($request->hasFile('files')) {
                // Multiple files upload (new functionality)
                $validator = Validator::make($request->all(), [
                    'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,mp4,mov|max:10240',
                ]);

                if ($validator->fails()) {
                    return $this->sendValidationError($validator->errors());
                }

                $uploadedFiles = [];
                foreach ($request->file('files') as $file) {
                    // Use Spatie Media Library to add the file
                    $media = $task->addMedia($file)
                        ->toMediaCollection('task-media', 'public');
                    
                    $uploadedFiles[] = $media;
                }

                return $this->sendResponse($uploadedFiles, 'Files uploaded successfully');
            } else {
                return $this->sendValidationError(['files' => 'No files provided']);
            }
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
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $media = $task->getMedia('task-media')->sortByDesc('created_at');

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
            // Find media by ID using Spatie Media Library
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
            
            if (!$media) {
                return $this->sendNotFound('Media not found');
            }

            // Ensure the media belongs to a task
            $task = Task::where('id', $media->model_id)
                ->first();

            if (!$task) {
                return $this->sendNotFound('Media not found');
            }

            // Delete the media using Spatie Media Library
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
                'media_ids.*' => 'integer'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            // Find media by IDs using Spatie Media Library
            $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::whereIn('id', $request->media_ids)->get();

            // Filter media that belongs to tasks
            $validMedia = $media->filter(function ($mediaItem) {
                $task = Task::where('id', $mediaItem->model_id)
                    ->first();
                return $task !== null;
            });

            // Delete the valid media
            foreach ($validMedia as $item) {
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

            // Allow user to delete their own messages, or admin/sub_admin/requester to delete any message
            if ($message->sender_id !== Auth::user()->id && !Auth::user()->hasRole(['admin', 'sub_admin', 'requester'])) {
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

            $messages = TaskMessage::whereIn('id', $request->message_ids)->get();

            foreach ($messages as $message) {
                // Allow user to delete their own messages, or admin/sub_admin/requester to delete any message
                if ($message->sent_by !== Auth::user()->id && !Auth::user()->hasRole(['admin', 'sub_admin', 'requester'])) {
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
     * Get question answers for a task
     */
    public function getQuestionAnswers($id)
    {
        try {
            $task = Task::with(['questionAnswers.briefQuestions'])
                ->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            return $this->sendResponse($task->questionAnswers, 'Question answers retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving question answers: ' . $e->getMessage());
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
     * Get checklist answers for a task
     */
    public function getChecklistAnswers($id)
    {
        try {
            $task = Task::with(['checklistAnswers.briefChecklist'])
                ->find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            return $this->sendResponse($task->checklistAnswers, 'Checklist answers retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving checklist answers: ' . $e->getMessage());
        }
    }

    /**
     * Get checklist status for a task
     */
    public function getChecklistStatus($id)
    {
        try {
            \Log::info("Getting checklist status for task ID: {$id}", [
                'user_id' => Auth::id(),
                'task_id' => $id,
                'request_url' => request()->fullUrl(),
                'user_agent' => request()->userAgent()
            ]);

            $task = Task::with(['checklistAnswers.briefChecklist'])
                ->find($id);

            if (!$task) {
                \Log::warning("Task not found for checklist status", [
                    'task_id' => $id,
                    'user_id' => Auth::id()
                ]);
                return $this->sendNotFound('Task not found');
            }

            // Calculate checklist completion status
            $checklistStatus = [];
            if ($task->checklistAnswers) {
                foreach ($task->checklistAnswers as $answer) {
                    $checklistStatus[] = [
                        'id' => $answer->id,
                        'checklist_id' => $answer->checklist_id,
                        'item_index' => $answer->checklist_answer['item_index'] ?? 0,
                        'completed' => $answer->checklist_answer['completed'] ?? false,
                        'notes' => $answer->checklist_answer['notes'] ?? '',
                        'answer_by' => $answer->answer_by,
                    ];
                }
            }

            \Log::info("Checklist status retrieved successfully", [
                'task_id' => $id,
                'checklist_count' => count($checklistStatus)
            ]);

            return $this->sendResponse($checklistStatus, 'Checklist status retrieved successfully');
        } catch (\Exception $e) {
            \Log::error("Error retrieving checklist status", [
                'task_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendServerError('Error retrieving checklist status: ' . $e->getMessage());
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
                'item_index' => 'required|integer|min:0',
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
                        'item_index' => $request->item_index,
                    ],
                ]
            );

            return $this->sendResponse($answer, 'Checklist answer submitted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error submitting checklist answer: ' . $e->getMessage());
        }
    }

    /**
     * Stop task repetition
     */
    public function stopRepetition($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            if (!$task->is_repeating) {
                return $this->sendError('This task is not a repeating task', [], 400);
            }

            // Check if user has permission to stop repetition
            $user = Auth::user();
            $canStop = $user->hasRole(['admin', 'requester']) || $task->created_by == $user->id;

            if (!$canStop) {
                return $this->sendError('You do not have permission to stop task repetition', [], 403);
            }

            $task->update(['repeat_active' => false]);

            return $this->sendResponse($task, 'Task repetition stopped successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error stopping task repetition: ' . $e->getMessage());
        }
    }

    /**
     * Resume task repetition
     */
    public function resumeRepetition($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            if (!$task->is_repeating) {
                return $this->sendError('This task is not a repeating task', [], 400);
            }

            // Check if user has permission to resume repetition
            $user = Auth::user();
            $canResume = $user->hasRole(['admin', 'requester']) || $task->created_by == $user->id;

            if (!$canResume) {
                return $this->sendError('You do not have permission to resume task repetition', [], 403);
            }

            $task->update(['repeat_active' => true]);

            return $this->sendResponse($task, 'Task repetition resumed successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error resuming task repetition: ' . $e->getMessage());
        }
    }

    /**
     * Get repeating task history
     */
    public function getRepeatingHistory($id)
    {
        try {
            $task = Task::find($id);

            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            if (!$task->is_repeating) {
                return $this->sendError('This task is not a repeating task', [], 400);
            }

            $childTasks = Task::where('parent_task_id', $id)
                ->with(['status', 'users'])
                ->orderBy('start_date', 'desc')
                ->get();

            return $this->sendResponse($childTasks, 'Repeating task history retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving repeating task history: ' . $e->getMessage());
        }
    }
}
