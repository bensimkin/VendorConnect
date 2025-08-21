<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\TaskBriefChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskBriefChecklistController extends BaseController
{
    /**
     * Get all task brief checklists
     */
    public function index(Request $request)
    {
        try {
            $query = TaskBriefChecklist::query();
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('item', 'like', "%{$search}%");
            }

            if ($request->has('template_id')) {
                $query->where('template_id', $request->template_id);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $checklists = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($checklists, 'Task brief checklists retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task brief checklists: ' . $e->getMessage());
        }
    }

    /**
     * Store a new task brief checklist
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_id' => 'required|exists:task_brief_templates,id',
                'item' => 'required|string',
                'description' => 'nullable|string',
                'required' => 'sometimes|boolean',
                'order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $checklist = TaskBriefChecklist::create([
                'template_id' => $request->template_id,
                'item' => $request->item,
                'description' => $request->description,
                'required' => $request->get('required', false),
                'order' => $request->get('order', 0),
                'workspace_id' => Auth::user()->workspace_id,
            ]);

            return $this->sendResponse($checklist, 'Task brief checklist created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating task brief checklist: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific task brief checklist
     */
    public function show($id)
    {
        try {
            $checklist = TaskBriefChecklist::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$checklist) {
                return $this->sendNotFound('Task brief checklist not found');
            }

            return $this->sendResponse($checklist, 'Task brief checklist retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task brief checklist: ' . $e->getMessage());
        }
    }

    /**
     * Update a task brief checklist
     */
    public function update(Request $request, $id)
    {
        try {
            $checklist = TaskBriefChecklist::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$checklist) {
                return $this->sendNotFound('Task brief checklist not found');
            }

            $validator = Validator::make($request->all(), [
                'template_id' => 'sometimes|required|exists:task_brief_templates,id',
                'item' => 'sometimes|required|string',
                'description' => 'nullable|string',
                'required' => 'sometimes|boolean',
                'order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $checklist->update($request->only(['template_id', 'item', 'description', 'required', 'order']));

            return $this->sendResponse($checklist, 'Task brief checklist updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating task brief checklist: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task brief checklist
     */
    public function destroy($id)
    {
        try {
            $checklist = TaskBriefChecklist::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$checklist) {
                return $this->sendNotFound('Task brief checklist not found');
            }

            $checklist->delete();

            return $this->sendResponse(null, 'Task brief checklist deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting task brief checklist: ' . $e->getMessage());
        }
    }
}
