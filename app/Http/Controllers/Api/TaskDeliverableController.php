<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Task;
use App\Models\TaskDeliverable;
use App\Models\Portfolio;
use App\Services\NotificationService;
use App\Models\TaskUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TaskDeliverableController extends BaseController
{
    /**
     * Get all deliverables for a task
     */
    public function index($taskId)
    {
        try {
            $task = Task::find($taskId);
            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            $deliverables = $task->deliverables()->with(['creator', 'media'])->get();
            return $this->sendResponse($deliverables, 'Deliverables retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving deliverables: ' . $e->getMessage());
        }
    }

    /**
     * Store a new deliverable
     */
    public function store(Request $request, $taskId)
    {
        try {
            $task = Task::find($taskId);
            if (!$task) {
                return $this->sendNotFound('Task not found');
            }

            // Check if task is past due with strict deadline
            if ($task->end_date && $task->close_deadline == 1) {
                $deadline = \Carbon\Carbon::parse($task->end_date);
                $current_time = now();
                
                if ($current_time > $deadline) {
                    return $this->sendError('Cannot add deliverables to a task that is past its strict deadline', [], 403);
                }
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:design,document,presentation,file,link,other',
                'google_link' => 'nullable|url',
                'external_link' => 'nullable|url',
                'files' => 'nullable|array',
                'files.*' => 'file|max:10240', // 10MB max per file
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $deliverable = TaskDeliverable::create([
                'task_id' => $taskId,
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'google_link' => $request->google_link,
                'external_link' => $request->external_link,
                'created_by' => $request->user()->id,
            ]);

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $deliverable->addMedia($file)
                        ->toMediaCollection('deliverable-files', 'public');
                }
            }

            // Create portfolio item automatically for each deliverable
            $task = Task::find($taskId);
            if ($task && $task->project && $task->project->clients->count() > 0) {
                $client = $task->project->clients->first();
                
                // Create a unique portfolio item for this specific deliverable
                $portfolio = Portfolio::create([
                    'client_id' => $client->id,
                    'task_id' => $taskId,
                    'project_id' => $task->project_id,
                    'deliverable_id' => $deliverable->id,
                    'title' => $deliverable->title,
                    'description' => $deliverable->description,
                    'deliverable_type' => $deliverable->type,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'created_by' => Auth::user()->id,
                ]);

                // Copy media files from deliverable to portfolio
                $deliverableMedia = $deliverable->getMedia('deliverable-files');
                foreach ($deliverableMedia as $media) {
                    $portfolio->addMedia($media->getPath())
                        ->toMediaCollection('portfolio-media');
                }
            }

            DB::commit();

            // Send notifications for new deliverable
            $notificationService = new NotificationService();
            $notificationService->deliverableAdded($deliverable, Auth::user());

            // Track activity for task
            TaskUser::updateActivity($taskId, Auth::id());

            $deliverable->load(['creator', 'media']);
            return $this->sendResponse($deliverable, 'Deliverable created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error creating deliverable: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific deliverable
     */
    public function show($taskId, $deliverableId)
    {
        try {
            $deliverable = TaskDeliverable::where('task_id', $taskId)
                ->with(['creator', 'media'])
                ->find($deliverableId);

            if (!$deliverable) {
                return $this->sendNotFound('Deliverable not found');
            }

            return $this->sendResponse($deliverable, 'Deliverable retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving deliverable: ' . $e->getMessage());
        }
    }

    /**
     * Update a deliverable
     */
    public function update(Request $request, $taskId, $deliverableId)
    {
        try {
            $deliverable = TaskDeliverable::where('task_id', $taskId)->find($deliverableId);
            if (!$deliverable) {
                return $this->sendNotFound('Deliverable not found');
            }

            // Check if task is past due with strict deadline
            $task = Task::find($taskId);
            if ($task && $task->end_date && $task->close_deadline == 1) {
                $deadline = \Carbon\Carbon::parse($task->end_date);
                $current_time = now();
                
                if ($current_time > $deadline) {
                    return $this->sendError('Cannot update deliverables for a task that is past its strict deadline', [], 403);
                }
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'sometimes|required|in:design,document,presentation,file,link,other',
                'google_link' => 'nullable|url',
                'external_link' => 'nullable|url',
                'files' => 'nullable|array',
                'files.*' => 'file|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $deliverable->update($request->only([
                'title', 'description', 'type', 'google_link', 'external_link'
            ]));

            // Handle additional file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $deliverable->addMedia($file)
                        ->toMediaCollection('deliverable-files', 'public');
                }
            }

            // Update portfolio item if it exists
            $existingPortfolio = Portfolio::where('deliverable_id', $deliverableId)->first();
            if ($existingPortfolio) {
                $existingPortfolio->update([
                    'title' => $deliverable->title,
                    'description' => $deliverable->description,
                    'deliverable_type' => $deliverable->type,
                ]);

                // Add new media files to portfolio
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $existingPortfolio->addMedia($file)
                            ->toMediaCollection('portfolio-media');
                    }
                }
            }

            DB::commit();

            // Track activity for task
            TaskUser::updateActivity($taskId, Auth::id());

            $deliverable->load(['creator', 'media']);
            return $this->sendResponse($deliverable, 'Deliverable updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error updating deliverable: ' . $e->getMessage());
        }
    }

    /**
     * Delete a deliverable
     */
    public function destroy($taskId, $deliverableId)
    {
        try {
            $deliverable = TaskDeliverable::where('task_id', $taskId)->find($deliverableId);
            if (!$deliverable) {
                return $this->sendNotFound('Deliverable not found');
            }

            $deliverable->delete();
            return $this->sendResponse(null, 'Deliverable deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting deliverable: ' . $e->getMessage());
        }
    }

    /**
     * Mark deliverable as completed
     */
    public function complete($taskId, $deliverableId)
    {
        try {
            $deliverable = TaskDeliverable::where('task_id', $taskId)->find($deliverableId);
            if (!$deliverable) {
                return $this->sendNotFound('Deliverable not found');
            }

            $deliverable->update(['completed_at' => now()]);
            
            // Track activity for task
            TaskUser::updateActivity($taskId, Auth::id());
            
            return $this->sendResponse($deliverable, 'Deliverable marked as completed');
        } catch (\Exception $e) {
            return $this->sendServerError('Error completing deliverable: ' . $e->getMessage());
        }
    }

    /**
     * Delete a file from deliverable
     */
    public function deleteFile($taskId, $deliverableId, $mediaId)
    {
        try {
            $deliverable = TaskDeliverable::where('task_id', $taskId)->find($deliverableId);
            if (!$deliverable) {
                return $this->sendNotFound('Deliverable not found');
            }

            $media = $deliverable->media()->find($mediaId);
            if (!$media) {
                return $this->sendNotFound('File not found');
            }

            $media->delete();
            return $this->sendResponse(null, 'File deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting file: ' . $e->getMessage());
        }
    }
}
