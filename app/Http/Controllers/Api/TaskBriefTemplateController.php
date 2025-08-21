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
                $query->where('template_name', 'like', "%{$search}%");
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $templates = $query->paginate($request->get('per_page', 15));

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
                'template_name' => 'required|string|max:255',
                'task_type_id' => 'required|exists:task_types,id',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $template = TaskBriefTemplates::create([
                'template_name' => $request->template_name,
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
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'template_data' => 'sometimes|required|json',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $template->update($request->only(['name', 'description', 'template_data', 'status']));

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
