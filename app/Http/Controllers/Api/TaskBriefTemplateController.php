<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\TaskBriefTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskBriefTemplateController extends BaseController
{
    /**
     * Get all task brief templates
     */
    public function index(Request $request)
    {
        try {
            $query = TaskBriefTemplates::query();
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('title', 'like', "%{$search}%");
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // If per_page is set to 'all', return all results without pagination
            if ($request->get('per_page') === 'all') {
                $templates = $query->with('taskType')->get();
                return $this->sendResponse($templates, 'Task brief templates retrieved successfully');
            }

            $perPage = $request->get('per_page', 15);
            $perPage = is_numeric($perPage) ? (int)$perPage : 15;
            $templates = $query->with('taskType')->paginate($perPage);

            return $this->sendPaginatedResponse($templates, 'Task brief templates retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task brief templates: ' . $e->getMessage());
        }
    }

    /**
     * Store a new task brief template
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'standard_brief' => 'nullable|string',
                'description' => 'nullable|string',
                'deliverable_quantity' => 'nullable|integer|min:1',
                'task_type_id' => 'required|exists:task_types,id',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $template = TaskBriefTemplates::create([
                'title' => $request->title,
                'standard_brief' => $request->standard_brief,
                'description' => $request->description,
                'deliverable_quantity' => $request->get('deliverable_quantity', 1),
                'task_type_id' => $request->task_type_id,
            ]);

            return $this->sendResponse($template, 'Task brief template created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating task brief template: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific task brief template
     */
    public function show($id)
    {
        try {
            $template = TaskBriefTemplates::find($id);

            if (!$template) {
                return $this->sendNotFound('Task brief template not found');
            }

            return $this->sendResponse($template, 'Task brief template retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task brief template: ' . $e->getMessage());
        }
    }

    /**
     * Update a task brief template
     */
    public function update(Request $request, $id)
    {
        try {
            $template = TaskBriefTemplates::find($id);

            if (!$template) {
                return $this->sendNotFound('Task brief template not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'standard_brief' => 'nullable|string',
                'description' => 'nullable|string',
                'deliverable_quantity' => 'nullable|integer|min:1',
                'task_type_id' => 'sometimes|required|exists:task_types,id',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $template->update($request->only(['title', 'standard_brief', 'description', 'deliverable_quantity', 'task_type_id']));

            return $this->sendResponse($template, 'Task brief template updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating task brief template: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task brief template
     */
    public function destroy($id)
    {
        try {
            $template = TaskBriefTemplate::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$template) {
                return $this->sendNotFound('Task brief template not found');
            }

            $template->delete();

            return $this->sendResponse(null, 'Task brief template deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting task brief template: ' . $e->getMessage());
        }
    }
}
