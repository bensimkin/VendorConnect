<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PriorityController extends BaseController
{
    /**
     * Get all priorities
     */
    public function index(Request $request)
    {
        try {
            $query = Priority::where('admin_id', Auth::user()->admin_id);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $priorities = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($priorities, 'Priorities retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving priorities: ' . $e->getMessage());
        }
    }

    /**
     * Store a new priority
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|max:7',
                'level' => 'nullable|integer|min:1|max:10',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $priority = Priority::create([
                'name' => $request->name,
                'description' => $request->description,
                'color' => $request->color,
                'level' => $request->level,
                'admin_id' => Auth::user()->admin_id,
                'status' => $request->get('status', 1),
            ]);

            return $this->sendResponse($priority, 'Priority created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating priority: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific priority
     */
    public function show($id)
    {
        try {
            $priority = Priority::where('admin_id', Auth::user()->admin_id)->find($id);

            if (!$priority) {
                return $this->sendNotFound('Priority not found');
            }

            return $this->sendResponse($priority, 'Priority retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving priority: ' . $e->getMessage());
        }
    }

    /**
     * Update a priority
     */
    public function update(Request $request, $id)
    {
        try {
            $priority = Priority::where('admin_id', Auth::user()->admin_id)->find($id);

            if (!$priority) {
                return $this->sendNotFound('Priority not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|max:7',
                'level' => 'nullable|integer|min:1|max:10',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $priority->update($request->only(['name', 'description', 'color', 'level', 'status']));

            return $this->sendResponse($priority, 'Priority updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating priority: ' . $e->getMessage());
        }
    }

    /**
     * Delete a priority
     */
    public function destroy($id)
    {
        try {
            $priority = Priority::where('admin_id', Auth::user()->admin_id)->find($id);

            if (!$priority) {
                return $this->sendNotFound('Priority not found');
            }

            // Check if priority is being used by tasks
            if ($priority->tasks()->count() > 0) {
                return $this->sendError('Cannot delete priority that is being used by tasks');
            }

            $priority->delete();

            return $this->sendResponse(null, 'Priority deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting priority: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple priorities
     */
    public function destroyMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'priority_ids' => 'required|array',
                'priority_ids.*' => 'exists:priorities,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $priorities = Priority::whereIn('id', $request->priority_ids)
                ->where('admin_id', Auth::user()->admin_id)
                ->get();

            foreach ($priorities as $priority) {
                // Check if priority is being used by tasks
                if ($priority->tasks()->count() > 0) {
                    continue; // Skip priorities that are being used
                }
                $priority->delete();
            }

            return $this->sendResponse(null, 'Priorities deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting priorities: ' . $e->getMessage());
        }
    }
}
