<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskTypeController extends BaseController
{
    /**
     * Get all task types
     */
    public function index(Request $request)
    {
        try {
            $query = TaskType::query();
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('task_type', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $taskTypes = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($taskTypes, 'Task types retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task types: ' . $e->getMessage());
        }
    }

    /**
     * Store a new task type
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'task_type' => 'required|string|max:255|unique:task_types,task_type',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $taskType = TaskType::create([
                'task_type' => $request->task_type,
            ]);

            return $this->sendResponse($taskType, 'Task type created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating task type: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific task type
     */
    public function show($id)
    {
        try {
            $taskType = TaskType::find($id);

            if (!$taskType) {
                return $this->sendNotFound('Task type not found');
            }

            return $this->sendResponse($taskType, 'Task type retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task type: ' . $e->getMessage());
        }
    }

    /**
     * Update a task type
     */
    public function update(Request $request, $id)
    {
        try {
            $taskType = TaskType::find($id);

            if (!$taskType) {
                return $this->sendNotFound('Task type not found');
            }

            $validator = Validator::make($request->all(), [
                'task_type' => 'sometimes|required|string|max:255|unique:task_types,task_type,' . $id,
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $taskType->update($request->only(['task_type']));

            return $this->sendResponse($taskType, 'Task type updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating task type: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task type
     */
    public function destroy($id)
    {
        try {
            $taskType = TaskType::find($id);

            if (!$taskType) {
                return $this->sendNotFound('Task type not found');
            }

            // Check if task type is being used by tasks
            if ($taskType->tasks()->count() > 0) {
                return $this->sendError('Cannot delete task type that is being used by tasks');
            }

            $taskType->delete();

            return $this->sendResponse(null, 'Task type deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting task type: ' . $e->getMessage());
        }
    }
}
