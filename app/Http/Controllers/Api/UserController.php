<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
{
    /**
     * Get all users with pagination
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['roles', 'permissions']);
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('role_id')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('role_id', $request->role_id);
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $users = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving users: ' . $e->getMessage());
        }
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => $request->get('status', 1),
            ]);

            // Assign roles
            if ($request->has('role_ids')) {
                $user->roles()->attach($request->role_ids);
            }

            DB::commit();

            $user->load(['roles', 'permissions']);

            return $this->sendResponse($user, 'User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific user
     */
    public function show($id)
    {
        try {
            $user = User::with(['roles', 'permissions'])
                ->where('workspace_id', Auth::user()->workspace_id)
                ->find($id);

            if (!$user) {
                return $this->sendNotFound('User not found');
            }

            return $this->sendResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving user: ' . $e->getMessage());
        }
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$user) {
                return $this->sendNotFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'role_ids' => 'sometimes|required|array',
                'role_ids.*' => 'exists:roles,id',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $updateData = $request->only(['first_name', 'last_name', 'email', 'phone', 'status']);
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Sync roles
            if ($request->has('role_ids')) {
                $user->roles()->sync($request->role_ids);
            }

            DB::commit();

            $user->load(['roles', 'permissions']);

            return $this->sendResponse($user, 'User updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error updating user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        try {
            $user = User::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$user) {
                return $this->sendNotFound('User not found');
            }

            // Prevent deleting self
            if ($user->id === Auth::user()->id) {
                return $this->sendError('Cannot delete your own account');
            }

            $user->delete();

            return $this->sendResponse(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple users
     */
    public function destroyMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $users = User::whereIn('id', $request->user_ids)
                ->where('workspace_id', Auth::user()->workspace_id)
                ->get();

            // Prevent deleting self
            $users = $users->filter(function ($user) {
                return $user->id !== Auth::user()->id;
            });

            foreach ($users as $user) {
                $user->delete();
            }

            return $this->sendResponse(null, 'Users deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting users: ' . $e->getMessage());
        }
    }
}
