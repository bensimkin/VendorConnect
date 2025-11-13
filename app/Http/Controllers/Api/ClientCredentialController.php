<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClientCredential;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientCredentialController extends BaseController
{
    /**
     * Get all credentials for a client
     */
    public function index($clientId)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            $client = Client::where('admin_id', $adminId)->find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $credentials = $client->credentials()->orderBy('created_at', 'desc')->get();
            
            return $this->sendResponse($credentials, 'Credentials retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving credentials: ' . $e->getMessage());
        }
    }

    /**
     * Store a new credential
     */
    public function store(Request $request, $clientId)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            $client = Client::where('admin_id', $adminId)->find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'url' => 'nullable|url|max:255',
                'username' => 'nullable|string|max:255',
                'password' => 'required|string',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $credential = $client->credentials()->create($request->all());

            return $this->sendResponse($credential, 'Credential created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating credential: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific credential
     */
    public function show($clientId, $credentialId)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Verify client belongs to this company
            $client = Client::where('admin_id', $adminId)->find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }
            
            $credential = ClientCredential::where('client_id', $clientId)
                ->where('id', $credentialId)
                ->first();

            if (!$credential) {
                return $this->sendNotFound('Credential not found');
            }

            return $this->sendResponse($credential, 'Credential retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving credential: ' . $e->getMessage());
        }
    }

    /**
     * Update a credential
     */
    public function update(Request $request, $clientId, $credentialId)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Verify client belongs to this company
            $client = Client::where('admin_id', $adminId)->find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }
            
            $credential = ClientCredential::where('client_id', $clientId)
                ->where('id', $credentialId)
                ->first();

            if (!$credential) {
                return $this->sendNotFound('Credential not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'url' => 'nullable|url|max:255',
                'username' => 'nullable|string|max:255',
                'password' => 'sometimes|required|string',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $credential->update($request->all());

            return $this->sendResponse($credential, 'Credential updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating credential: ' . $e->getMessage());
        }
    }

    /**
     * Delete a credential
     */
    public function destroy($clientId, $credentialId)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Verify client belongs to this company
            $client = Client::where('admin_id', $adminId)->find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }
            
            $credential = ClientCredential::where('client_id', $clientId)
                ->where('id', $credentialId)
                ->first();

            if (!$credential) {
                return $this->sendNotFound('Credential not found');
            }

            $credential->delete();

            return $this->sendResponse(null, 'Credential deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting credential: ' . $e->getMessage());
        }
    }

    /**
     * Get decrypted password for a credential
     */
    public function getPassword($clientId, $credentialId)
    {
        try {
            $adminId = getAdminIdByUserRole();
            
            // Verify client belongs to this company
            $client = Client::where('admin_id', $adminId)->find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }
            
            $credential = ClientCredential::where('client_id', $clientId)
                ->where('id', $credentialId)
                ->first();

            if (!$credential) {
                return $this->sendNotFound('Credential not found');
            }

            return $this->sendResponse([
                'password' => $credential->getPassword()
            ], 'Password retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving password: ' . $e->getMessage());
        }
    }
}
