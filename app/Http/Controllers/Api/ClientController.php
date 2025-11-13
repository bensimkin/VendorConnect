<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Client;
use App\Models\Task;
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
            $user = Auth::user();
            $adminId = getAdminIdByUserRole();
            
            $query = Client::where('admin_id', $adminId);

            // Role-based filtering
            if ($user->hasRole(['admin', 'sub_admin', 'sub admin'])) {
                // Admins and sub-admins see all clients (no additional filtering)
            } elseif ($user->hasRole('Requester')) {
                // Requesters only see clients related to their projects
                $query->whereHas('projects', function($q) use ($user) {
                    $q->where('created_by', $user->id);
                });
            } elseif ($user->hasRole('Tasker')) {
                // Taskers only see clients related to projects they're assigned to
                $query->whereHas('projects.tasks', function($q) use ($user) {
                    $q->whereHas('users', function($subQ) use ($user) {
                        $subQ->where('users.id', $user->id);
                    });
                });
            }

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search, $user) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%");

                    // Only admins and sub-admins can search by email
                    if ($user->hasRole(['admin', 'sub admin'])) {
                        $q->orWhere('email', 'like', "%{$search}%");
                    }
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id')) {
                // $query->whereHas('users', function ($q) use ($request) {
                //     $q->where('user_id', $request->user_id);
                // });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Support returning all records without pagination for dropdowns
            if ($request->get('per_page') === 'all') {
                $clients = $query->withCount(['projects as projects_count'])
                    ->withCount(['tasks as tasks_count'])
                    ->withCount(['projects as active_projects' => function($query) {
                        $query->where('status_id', 20); // Active status ID
                    }])
                    ->get();
            } else {
                $clients = $query->withCount(['projects as projects_count'])
                    ->withCount(['tasks as tasks_count'])
                    ->withCount(['projects as active_projects' => function($query) {
                        $query->where('status_id', 20); // Active status ID
                    }])
                    ->paginate($request->get('per_page', 15));
            }

            // Apply role-based data protection to the response
            if (!$user->hasRole(['admin', 'sub admin'])) {
                // Remove sensitive data for requesters and taskers
                if ($request->get('per_page') === 'all') {
                    $clients->transform(function ($client) {
                        unset($client->email);
                        unset($client->phone);
                        unset($client->address);
                        unset($client->city);
                        unset($client->state);
                        unset($client->country);
                        unset($client->zip);
                        unset($client->dob);
                        unset($client->notes);
                        return $client;
                    });
                } else {
                    $clients->getCollection()->transform(function ($client) {
                        unset($client->email);
                        unset($client->phone);
                        unset($client->address);
                        unset($client->city);
                        unset($client->state);
                        unset($client->country);
                        unset($client->zip);
                        unset($client->dob);
                        unset($client->notes);
                        return $client;
                    });
                }
            }

            // Return appropriate response based on pagination
            if ($request->get('per_page') === 'all') {
                return $this->sendResponse($clients, 'Clients retrieved successfully');
            } else {
                return $this->sendPaginatedResponse($clients, 'Clients retrieved successfully');
            }
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
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:clients,email',
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'website' => 'nullable|url',
                'notes' => 'nullable|string',
                'status' => 'sometimes|boolean',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'zip' => 'nullable|string|max:255',
                'dob' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            // Get admin_id for current user
            $adminId = getAdminIdByUserRole();

            $client = Client::create([
                'admin_id' => $adminId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company' => $request->company,
                'address' => $request->address,
                'notes' => $request->notes,
                'status' => $request->get('status', 1),
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip' => $request->zip,
                'dob' => $request->dob,
            ]);

            // Attach users
            if ($request->has('user_ids')) {
                // $client->users()->attach($request->user_ids);
            }

            DB::commit();

            // $client->load(['users', 'tasks']);

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
            \Log::info('=== CLIENT SHOW REQUEST START ===');
            \Log::info('ClientController::show called with ID: ' . $id);
            \Log::info('Request method: ' . request()->method());
            \Log::info('Request URL: ' . request()->fullUrl());
            \Log::info('Request headers: ' . json_encode(request()->headers->all()));
            \Log::info('Auth user: ' . json_encode(Auth::user()));
            \Log::info('Auth check: ' . (Auth::check() ? 'true' : 'false'));
            
            // Check if client exists first
            $clientExists = Client::where('id', $id)->exists();
            \Log::info('Client exists in database: ' . ($clientExists ? 'true' : 'false'));
            
            if (!$clientExists) {
                \Log::warning('Client with ID ' . $id . ' does not exist in database');
                return $this->sendNotFound('Client not found');
            }
            
            $client = Client::with(['tasks'])
                ->find($id);

            \Log::info('Client query result: ' . ($client ? 'found' : 'not found'));
            
            if (!$client) {
                \Log::warning('Client not found with ID: ' . $id);
                return $this->sendNotFound('Client not found');
            }

            // Apply role-based data protection
            $currentUser = Auth::user();
            if (!$currentUser->hasRole(['admin', 'sub_admin'])) {
                // Remove sensitive data for requesters and taskers
                unset($client->email);
                unset($client->phone);
                unset($client->address);
                unset($client->city);
                unset($client->state);
                unset($client->country);
                unset($client->zip);
                unset($client->dob);
                unset($client->notes);
            }

            \Log::info('Client data: ' . json_encode($client->toArray()));
            \Log::info('=== CLIENT SHOW REQUEST SUCCESS ===');
            return $this->sendResponse($client, 'Client retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('=== CLIENT SHOW REQUEST ERROR ===');
            \Log::error('ClientController::show error: ' . $e->getMessage());
            \Log::error('Error class: ' . get_class($e));
            \Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== CLIENT SHOW REQUEST ERROR END ===');
            return $this->sendServerError('Error retrieving client: ' . $e->getMessage());
        }
    }

    /**
     * Update a client
     */
    public function update(Request $request, $id)
    {
        try {
            $client = Client::find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:clients,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'company' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'website' => 'nullable|url',
                'notes' => 'nullable|string',
                'status' => 'sometimes|boolean',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'zip' => 'nullable|string|max:255',
                'dob' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $updateData = $request->only(['first_name', 'last_name', 'email', 'phone', 'company', 'address', 'status', 'city', 'state', 'country', 'zip', 'dob']);

            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }

            $client->update($updateData);

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
            $client = Client::find($id);

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

            $clients = Client::whereIn('id', $request->client_ids)->get();

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
            \Log::info('=== CLIENT PROJECTS REQUEST START ===');
            \Log::info('ClientController::projects called with ID: ' . $id);
            \Log::info('Request method: ' . request()->method());
            \Log::info('Request URL: ' . request()->fullUrl());
            \Log::info('Auth user: ' . json_encode(Auth::user()));
            
            $client = Client::find($id);
            \Log::info('Client found: ' . ($client ? 'yes' : 'no'));

            if (!$client) {
                \Log::warning('Client not found with ID: ' . $id);
                return $this->sendNotFound('Client not found');
            }

            \Log::info('Fetching projects for client: ' . $client->id);
            $projects = $client->projects()->with(['status', 'client'])->paginate(15);
            \Log::info('Projects query executed successfully');
            \Log::info('Projects count: ' . $projects->count());
            \Log::info('Projects data: ' . json_encode($projects->toArray()));

            \Log::info('=== CLIENT PROJECTS REQUEST SUCCESS ===');
            return $this->sendPaginatedResponse($projects, 'Client projects retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('=== CLIENT PROJECTS REQUEST ERROR ===');
            \Log::error('ClientController::projects error: ' . $e->getMessage());
            \Log::error('Error class: ' . get_class($e));
            \Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== CLIENT PROJECTS REQUEST ERROR END ===');
            return $this->sendServerError('Error retrieving client projects: ' . $e->getMessage());
        }
    }

    /**
     * Get client tasks
     */
    public function tasks($id)
    {
        try {
            \Log::info('=== CLIENT TASKS REQUEST START ===');
            \Log::info('ClientController::tasks called with ID: ' . $id);
            \Log::info('Request method: ' . request()->method());
            \Log::info('Request URL: ' . request()->fullUrl());
            \Log::info('Auth user: ' . json_encode(Auth::user()));
            
            $client = Client::find($id);
            \Log::info('Client found: ' . ($client ? 'yes' : 'no'));

            if (!$client) {
                \Log::warning('Client not found with ID: ' . $id);
                return $this->sendNotFound('Client not found');
            }

            \Log::info('Fetching tasks for client: ' . $client->id);
            \Log::info('Client projects count: ' . $client->projects()->count());
            
            $adminId = getAdminIdByUserRole();
            
            // Get tasks for this client via projects
            $tasks = Task::where('admin_id', $adminId)->whereHas('project', function($q) use ($client) {
                $q->whereHas('clients', function($subQ) use ($client) {
                    $subQ->where('clients.id', $client->id);
                });
            })
            ->with(['status', 'priority', 'project'])
            ->paginate(15);
            
            \Log::info('Tasks query executed successfully');
            \Log::info('Tasks count: ' . $tasks->count());
            \Log::info('Tasks data: ' . json_encode($tasks->toArray()));

            \Log::info('=== CLIENT TASKS REQUEST SUCCESS ===');
            return $this->sendPaginatedResponse($tasks, 'Client tasks retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('=== CLIENT TASKS REQUEST ERROR ===');
            \Log::error('ClientController::tasks error: ' . $e->getMessage());
            \Log::error('Error class: ' . get_class($e));
            \Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== CLIENT TASKS REQUEST ERROR END ===');
            return $this->sendServerError('Error retrieving client tasks: ' . $e->getMessage());
        }
    }

    /**
     * Get client brief and files
     */
    public function getClientBriefAndFiles($id)
    {
        try {
            $client = Client::with(['brandGuideFiles', 'generalClientFiles'])->find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            // Check permissions
            $user = Auth::user();
            if (!$this->hasAdminAccess($user) && !$user->hasRole('Requester')) {
                return $this->sendUnauthorized('You do not have permission to view client brief and files');
            }

            return $this->sendResponse([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_brief' => $client->client_brief,
                'brand_guide_files' => $client->brandGuideFiles->map(function($file) {
                    return [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_path' => $file->file_path,
                        'file_type' => $file->file_type,
                        'file_size' => $file->file_size,
                        'description' => $file->description,
                        'created_at' => $file->created_at,
                    ];
                }),
                'client_files' => $client->generalClientFiles->map(function($file) {
                    return [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'file_path' => $file->file_path,
                        'file_type' => $file->file_type,
                        'file_size' => $file->file_size,
                        'description' => $file->description,
                        'created_at' => $file->created_at,
                    ];
                }),
            ], 'Client brief and files retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving client brief and files: ' . $e->getMessage());
        }
    }

    /**
     * Update client brief
     */
    public function updateClientBrief(Request $request, $id)
    {
        try {
            $client = Client::find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            // Check permissions
            $user = Auth::user();
            if (!$this->hasAdminAccess($user) && !$user->hasRole('Requester')) {
                return $this->sendUnauthorized('You do not have permission to update client brief');
            }

            $validator = Validator::make($request->all(), [
                'client_brief' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $client->update([
                'client_brief' => $request->client_brief
            ]);

            return $this->sendResponse([
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_brief' => $client->client_brief,
            ], 'Client brief updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating client brief: ' . $e->getMessage());
        }
    }

    /**
     * Upload client file
     */
    public function uploadClientFile(Request $request, $id)
    {
        try {
            $client = Client::find($id);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            // Check permissions
            $user = Auth::user();
            if (!$this->hasAdminAccess($user) && !$user->hasRole('Requester')) {
                return $this->sendUnauthorized('You do not have permission to upload files');
            }

            $validator = Validator::make($request->all(), [
                'file' => 'required|file|max:10240', // 10MB max
                'file_category' => 'required|in:brand_guide,client_file',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('client_files/' . $client->id, $fileName, 'public');

            $clientFile = $client->clientFiles()->create([
                'file_path' => $filePath,
                'file_category' => $request->file_category,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
            ]);

            return $this->sendResponse([
                'file_id' => $clientFile->id,
                'client_id' => $client->id,
                'client_name' => $client->name,
                'file_name' => $clientFile->file_name,
                'file_path' => $clientFile->file_path,
                'file_category' => $clientFile->file_category,
            ], 'File uploaded successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error uploading file: ' . $e->getMessage());
        }
    }

    /**
     * Delete client file
     */
    public function deleteClientFile($clientId, $fileId)
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            // Check permissions
            $user = Auth::user();
            if (!$this->hasAdminAccess($user) && !$user->hasRole('Requester')) {
                return $this->sendUnauthorized('You do not have permission to delete files');
            }

            $file = $client->clientFiles()->find($fileId);

            if (!$file) {
                return $this->sendNotFound('File not found');
            }

            // Delete the physical file
            if (\Storage::disk('public')->exists($file->file_path)) {
                \Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();

            return $this->sendResponse(null, 'File deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting file: ' . $e->getMessage());
        }
    }
}
