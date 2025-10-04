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
            // Single-tenant: do not filter by admin_id
            $query = Priority::query();

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // No status filter on priorities in current schema

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Support returning all without pagination
            if ($request->get('per_page') === 'all') {
                $priorities = $query->get();
                return $this->sendResponse($priorities, 'Priorities retrieved successfully');
            }

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
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $priority = Priority::create([
                'title' => $request->title,
                'slug' => $request->slug,
                'admin_id' => Auth::user()->admin_id ?? null,
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
            $priority = Priority::where('admin_id', Auth::user()->admin_id ?? 1)->find($id);

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
            $priority = Priority::where('admin_id', Auth::user()->admin_id ?? 1)->find($id);

            if (!$priority) {
                return $this->sendNotFound('Priority not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $priority->update($request->only(['title', 'slug']));

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
            $priority = Priority::where('admin_id', Auth::user()->admin_id ?? 1)->find($id);

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
                ->where('admin_id', Auth::user()->admin_id ?? 1)
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
