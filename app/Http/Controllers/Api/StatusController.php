<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StatusController extends BaseController
{
    /**
     * Get all statuses
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Status::query();
            // Remove admin_id filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            if ($user->hasRole('Tasker')) {
                $query->whereIn('slug', ['pending', 'submitted', 'rejected', 'in-progress']);
            }

            if ($request->get('per_page') === 'all') {
                $statuses = $query->get();
                return $this->sendResponse($statuses, 'Statuses retrieved successfully');
            }

            $statuses = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($statuses, 'Statuses retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving statuses: ' . $e->getMessage());
        }
    }

    /**
     * Store a new status
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

            $status = Status::create([
                'title' => $request->title,
                'slug' => $request->slug,
                'admin_id' => Auth::user()->admin_id ?? null,
            ]);

            return $this->sendResponse($status, 'Status created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating status: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific status
     */
    public function show($id)
    {
        try {
            $status = Status::find($id);

            if (!$status) {
                return $this->sendNotFound('Status not found');
            }

            return $this->sendResponse($status, 'Status retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving status: ' . $e->getMessage());
        }
    }

    /**
     * Update a status
     */
    public function update(Request $request, $id)
    {
        try {
            $status = Status::find($id);

            if (!$status) {
                return $this->sendNotFound('Status not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'slug' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $status->update($request->only(['title', 'slug']));

            return $this->sendResponse($status, 'Status updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating status: ' . $e->getMessage());
        }
    }

    /**
     * Delete a status
     */
    public function destroy($id)
    {
        try {
            $status = Status::find($id);

            if (!$status) {
                return $this->sendNotFound('Status not found');
            }

            // Check if status is being used by tasks or projects
            if ($status->tasks()->count() > 0 || $status->projects()->count() > 0) {
                return $this->sendError('Cannot delete status that is being used by tasks or projects');
            }

            $status->delete();

            return $this->sendResponse(null, 'Status deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting status: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple statuses
     */
    public function destroyMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status_ids' => 'required|array',
                'status_ids.*' => 'exists:statuses,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $statuses = Status::whereIn('id', $request->status_ids)->get();

            foreach ($statuses as $status) {
                // Check if status is being used by tasks
                if ($status->tasks()->count() > 0) {
                    continue; // Skip statuses that are being used
                }
                $status->delete();
            }

            return $this->sendResponse(null, 'Statuses deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting statuses: ' . $e->getMessage());
        }
    }
}
