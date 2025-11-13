<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ApiKeyController extends BaseController
{
    /**
     * Get all API keys for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $adminId = getAdminIdByUserRole();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can view API keys', 403);
            }
            
            $apiKeys = ApiKey::where('admin_id', $adminId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($key) {
                    return [
                        'id' => $key->id,
                        'name' => $key->name,
                        'description' => $key->description,
                        'masked_key' => $key->masked_key,
                        'permissions' => $key->permissions,
                        'last_used_at' => $key->last_used_at,
                        'expires_at' => $key->expires_at,
                        'is_active' => $key->is_active,
                        'created_at' => $key->created_at,
                    ];
                });

            return $this->sendResponse($apiKeys, 'API keys retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving API keys: ' . $e->getMessage());
        }
    }

    /**
     * Create a new API key
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|in:create,read,update,delete',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $user = $request->user();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can create API keys', 403);
            }
            
            $adminId = getAdminIdByUserRole();
            
            // Check if user already has too many API keys (limit to 10)
            $existingKeysCount = ApiKey::where('admin_id', $adminId)->count();
            if ($existingKeysCount >= 10) {
                return $this->sendError('Maximum number of API keys reached (10)', 400);
            }

            $expiresAt = $request->expires_at ? Carbon::parse($request->expires_at) : null;
            
            $apiKey = ApiKey::createForUser(
                $user,
                $request->name,
                $request->description,
                $request->permissions,
                $expiresAt
            );

            // Return the full key only on creation
            $responseData = [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'description' => $apiKey->description,
                'key' => $apiKey->key, // Full key only returned on creation
                'permissions' => $apiKey->permissions,
                'expires_at' => $apiKey->expires_at,
                'is_active' => $apiKey->is_active,
                'created_at' => $apiKey->created_at,
            ];

            return $this->sendResponse($responseData, 'API key created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating API key: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific API key
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $adminId = getAdminIdByUserRole();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can view API keys', 403);
            }
            
            $apiKey = ApiKey::where('admin_id', $adminId)->find($id);

            if (!$apiKey) {
                return $this->sendNotFound('API key not found');
            }

            $responseData = [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'description' => $apiKey->description,
                'masked_key' => $apiKey->masked_key,
                'permissions' => $apiKey->permissions,
                'last_used_at' => $apiKey->last_used_at,
                'expires_at' => $apiKey->expires_at,
                'is_active' => $apiKey->is_active,
                'created_at' => $apiKey->created_at,
            ];

            return $this->sendResponse($responseData, 'API key retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving API key: ' . $e->getMessage());
        }
    }

    /**
     * Update an API key
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|in:create,read,update,delete',
                'expires_at' => 'nullable|date|after:now',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $user = $request->user();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can update API keys', 403);
            }
            
            $adminId = getAdminIdByUserRole();
            
            $apiKey = ApiKey::where('admin_id', $adminId)->find($id);

            if (!$apiKey) {
                return $this->sendNotFound('API key not found');
            }

            $updateData = $request->only(['name', 'description', 'permissions', 'is_active']);
            
            if ($request->has('expires_at')) {
                $updateData['expires_at'] = $request->expires_at ? Carbon::parse($request->expires_at) : null;
            }

            $apiKey->update($updateData);

            $responseData = [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'description' => $apiKey->description,
                'masked_key' => $apiKey->masked_key,
                'permissions' => $apiKey->permissions,
                'last_used_at' => $apiKey->last_used_at,
                'expires_at' => $apiKey->expires_at,
                'is_active' => $apiKey->is_active,
                'created_at' => $apiKey->created_at,
            ];

            return $this->sendResponse($responseData, 'API key updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating API key: ' . $e->getMessage());
        }
    }

    /**
     * Delete an API key
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can delete API keys', 403);
            }
            
            $adminId = getAdminIdByUserRole();
            
            $apiKey = ApiKey::where('admin_id', $adminId)->find($id);

            if (!$apiKey) {
                return $this->sendNotFound('API key not found');
            }

            $apiKey->delete();

            return $this->sendResponse(null, 'API key deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting API key: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate an API key
     */
    public function regenerate(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can regenerate API keys', 403);
            }
            
            $adminId = getAdminIdByUserRole();
            
            $apiKey = ApiKey::where('admin_id', $adminId)->find($id);

            if (!$apiKey) {
                return $this->sendNotFound('API key not found');
            }

            $apiKey->update(['key' => ApiKey::generateKey($user, $apiKey->permissions, $apiKey->expires_at)]);

            $responseData = [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'description' => $apiKey->description,
                'key' => $apiKey->key, // Return full key after regeneration
                'permissions' => $apiKey->permissions,
                'expires_at' => $apiKey->expires_at,
                'is_active' => $apiKey->is_active,
                'created_at' => $apiKey->created_at,
            ];

            return $this->sendResponse($responseData, 'API key regenerated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error regenerating API key: ' . $e->getMessage());
        }
    }

    /**
     * Get API key usage statistics
     */
    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            
            // Check if user is admin
            if (!$user->hasRole('admin')) {
                return $this->sendError('Only administrators can view API key statistics', 403);
            }
            
            $adminId = getAdminIdByUserRole();
            
            $totalKeys = ApiKey::where('admin_id', $adminId)->count();
            $activeKeys = ApiKey::where('admin_id', $adminId)->active()->count();
            $expiredKeys = ApiKey::where('admin_id', $adminId)
                ->where('expires_at', '<', now())
                ->count();
            
            $recentlyUsed = ApiKey::where('admin_id', $adminId)
                ->whereNotNull('last_used_at')
                ->where('last_used_at', '>=', now()->subDays(30))
                ->count();

            $stats = [
                'total_keys' => $totalKeys,
                'active_keys' => $activeKeys,
                'expired_keys' => $expiredKeys,
                'recently_used' => $recentlyUsed,
                'max_keys' => 10,
            ];

            return $this->sendResponse($stats, 'API key statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving API key statistics: ' . $e->getMessage());
        }
    }
}

