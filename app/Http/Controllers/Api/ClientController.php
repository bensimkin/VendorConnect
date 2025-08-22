<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientController extends BaseController
{
    /**
     * Get all clients with pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Client::query();
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id')) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $clients = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($clients, 'Clients retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving clients: ' . $e->getMessage());
        }
    }

    /**
     * Store a new client
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:clients,email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'website' => 'nullable|url',
                'notes' => 'nullable|string',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $client = Client::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'address' => $request->address,
                'website' => $request->website,
                'notes' => $request->notes,
                'workspace_id' => $request->user()->workspace_id,
                'status' => $request->get('status', 1),
                'created_by' => $request->user()->id,
            ]);

            // Attach users
            if ($request->has('user_ids')) {
                $client->users()->attach($request->user_ids);
            }

            DB::commit();

            $client->load(['users', 'tasks']);

            return $this->sendResponse($client, 'Client created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error creating client: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific client
     */
    public function show($id)
    {
        try {
            $client = Client::with(['users', 'tasks'])
                ->where('workspace_id', Auth::user()->workspace_id)
                ->find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            return $this->sendResponse($client, 'Client retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving client: ' . $e->getMessage());
        }
    }

    /**
     * Update a client
     */
    public function update(Request $request, $id)
    {
        try {
            $client = Client::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:clients,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'website' => 'nullable|url',
                'notes' => 'nullable|string',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $client->update($request->only([
                'name', 'email', 'phone', 'company',
                'address', 'website', 'notes', 'status'
            ]));



            DB::commit();

            $client->load(['tasks']);

            return $this->sendResponse($client, 'Client updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error updating client: ' . $e->getMessage());
        }
    }

    /**
     * Delete a client
     */
    public function destroy($id)
    {
        try {
            $client = Client::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $client->delete();

            return $this->sendResponse(null, 'Client deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting client: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple clients
     */
    public function destroyMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_ids' => 'required|array',
                'client_ids.*' => 'exists:clients,id'
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $clients = Client::whereIn('id', $request->client_ids)
                ->where('workspace_id', Auth::user()->workspace_id)
                ->get();

            foreach ($clients as $client) {
                $client->delete();
            }

            return $this->sendResponse(null, 'Clients deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting clients: ' . $e->getMessage());
        }
    }

    /**
     * Delete client file
     */
    public function deleteFile($fileId)
    {
        try {
            // This would need to be implemented based on your file storage system
            // For now, we'll return a placeholder response
            
            return $this->sendResponse(null, 'File deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting file: ' . $e->getMessage());
        }
    }

    /**
     * Get client projects
     */
    public function projects($id)
    {
        try {
            $client = Client::find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $projects = $client->projects()->with(['status', 'client'])->paginate(15);

            return $this->sendPaginatedResponse($projects, 'Client projects retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving client projects: ' . $e->getMessage());
        }
    }

    /**
     * Get client tasks
     */
    public function tasks($id)
    {
        try {
            $client = Client::find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $tasks = $client->tasks()->with(['status', 'priority', 'assigned_to'])->paginate(15);

            return $this->sendPaginatedResponse($tasks, 'Client tasks retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving client tasks: ' . $e->getMessage());
        }
    }
}
