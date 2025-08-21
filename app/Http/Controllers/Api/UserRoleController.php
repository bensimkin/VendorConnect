<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends BaseController
{
    /**
     * Get all user roles
     */
    public function index(Request $request)
    {
        try {
            $query = Role::with(['permissions']);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $roles = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($roles, 'User roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving user roles: ' . $e->getMessage());
        }
    }

    /**
     * Store a new user role
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name',
                'description' => 'nullable|string',
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            // Assign permissions
            if ($request->has('permission_ids')) {
                $role->permissions()->attach($request->permission_ids);
            }

            $role->load(['permissions']);

            return $this->sendResponse($role, 'User role created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating user role: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific user role
     */
    public function show($id)
    {
        try {
            $role = Role::with(['permissions'])->find($id);

            if (!$role) {
                return $this->sendNotFound('User role not found');
            }

            return $this->sendResponse($role, 'User role retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving user role: ' . $e->getMessage());
        }
    }

    /**
     * Update a user role
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendNotFound('User role not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
                'description' => 'nullable|string',
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $role->update($request->only(['name', 'description']));

            // Sync permissions
            if ($request->has('permission_ids')) {
                $role->permissions()->sync($request->permission_ids);
            }

            $role->load(['permissions']);

            return $this->sendResponse($role, 'User role updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating user role: ' . $e->getMessage());
        }
    }

    /**
     * Delete a user role
     */
    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendNotFound('User role not found');
            }

            // Check if role is being used by users
            if ($role->users()->count() > 0) {
                return $this->sendError('Cannot delete role that is assigned to users');
            }

            $role->delete();

            return $this->sendResponse(null, 'User role deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting user role: ' . $e->getMessage());
        }
    }
}
