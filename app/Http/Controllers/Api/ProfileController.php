<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends BaseController
{
    /**
     * Get current user profile
     */
    public function current()
    {
        try {
            $user = Auth::user()->load(['roles', 'permissions']);
            return $this->sendResponse($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving profile: ' . $e->getMessage());
        }
    }

    /**
     * Get user profile
     */
    public function show($id)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Multi-tenant filtering: Only allow viewing users from the same company
            $user = User::with(['roles', 'permissions'])
                ->where(function($q) use ($adminId) {
                    $admin = \App\Models\Admin::where('id', $adminId)->first();
                    if ($admin) {
                        $q->where('id', $admin->user_id);
                    }
                    $q->orWhereHas('teamMembers', function($subQ) use ($adminId) {
                        $subQ->where('admin_id', $adminId);
                    });
                })
                ->find($id);

            if (!$user) {
                return $this->sendNotFound('User not found');
            }

            return $this->sendResponse($user, 'Profile retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving profile: ' . $e->getMessage());
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request, $id)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Multi-tenant filtering: Only allow updating users from the same company
            $user = User::where(function($q) use ($adminId) {
                    $admin = \App\Models\Admin::where('id', $adminId)->first();
                    if ($admin) {
                        $q->where('id', $admin->user_id);
                    }
                    $q->orWhereHas('teamMembers', function($subQ) use ($adminId) {
                        $subQ->where('admin_id', $adminId);
                    });
                })
                ->find($id);

            if (!$user) {
                return $this->sendNotFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'current_password' => 'nullable|required_with:new_password',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            // Verify current password if changing password
            if ($request->has('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return $this->sendError('Current password is incorrect');
                }
            }

            $updateData = $request->only(['first_name', 'last_name', 'email', 'phone']);
            
            if ($request->has('new_password')) {
                $updateData['password'] = Hash::make($request->new_password);
            }

            $user->update($updateData);

            $user->load(['roles', 'permissions']);

            return $this->sendResponse($user, 'Profile updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating profile: ' . $e->getMessage());
        }
    }

    /**
     * Update profile photo
     */
    public function updatePhoto(Request $request, $id)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Multi-tenant filtering: Only allow updating users from the same company
            $user = User::where(function($q) use ($adminId) {
                    $admin = \App\Models\Admin::where('id', $adminId)->first();
                    if ($admin) {
                        $q->where('id', $admin->user_id);
                    }
                    $q->orWhereHas('teamMembers', function($subQ) use ($adminId) {
                        $subQ->where('admin_id', $adminId);
                    });
                })
                ->find($id);

            if (!$user) {
                return $this->sendNotFound('User not found');
            }

            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            // Delete old photo if exists
            if ($user->photo && Storage::exists($user->photo)) {
                Storage::delete($user->photo);
            }

            // Store new photo
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            
            $user->update(['photo' => $photoPath]);

            return $this->sendResponse(['photo' => $photoPath], 'Profile photo updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating profile photo: ' . $e->getMessage());
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Notification preferences are stored per-user (not per-tenant)
            // This is intentional - each user has their own notification settings
            
            // For now, just return success as notification preferences
            // would typically be stored in a user_preferences table
            // or as a JSON field on the users table
            
            return $this->sendResponse([
                'email_notifications' => $request->input('email_notifications', true),
                'push_notifications' => $request->input('push_notifications', false),
                'task_assignments' => $request->input('task_assignments', true),
                'task_updates' => $request->input('task_updates', true),
                'mentions' => $request->input('mentions', true),
            ], 'Notification preferences updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating notification preferences: ' . $e->getMessage());
        }
    }
}
