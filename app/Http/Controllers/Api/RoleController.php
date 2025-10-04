<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends BaseController
{
    /**
     * Get all roles
     */
    public function index(Request $request)
    {
        try {
            $query = Role::query();

            // Apply search filter
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $roles = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($roles, 'Roles retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving roles: ' . $e->getMessage());
        }
    }

    /**
     * Store a new role
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name',
                'guard_name' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->get('guard_name', 'web'),
            ]);

            return $this->sendResponse($role, 'Role created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating role: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific role
     */
    public function show($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendNotFound('Role not found');
            }

            return $this->sendResponse($role, 'Role retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving role: ' . $e->getMessage());
        }
    }

    /**
     * Update a role
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendNotFound('Role not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
                'guard_name' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $role->update($request->only(['name', 'guard_name']));

            return $this->sendResponse($role, 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating role: ' . $e->getMessage());
        }
    }

    /**
     * Delete a role
     */
    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendNotFound('Role not found');
            }

            $role->delete();

            return $this->sendResponse(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting role: ' . $e->getMessage());
        }
    }
}
