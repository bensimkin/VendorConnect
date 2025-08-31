<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Portfolio;
use App\Models\Client;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends BaseController
{
    /**
     * Get all portfolios with pagination and filtering
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Portfolio::with(['client', 'task', 'project', 'createdBy', 'taskType']);

            // Role-based filtering
            if ($user->hasRole('Requester')) {
                // Requesters see portfolio items related to tasks they created OR were assigned to
                $query->whereHas('task', function($q) use ($user) {
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('created_by', $user->id)
                              ->orWhereHas('users', function($taskUserQ) use ($user) {
                                  $taskUserQ->where('users.id', $user->id);
                              });
                    });
                });
            } elseif ($user->hasRole('Tasker')) {
                // Taskers see portfolio items from tasks they're currently assigned to
                $query->whereHas('task', function($q) use ($user) {
                    $q->whereHas('users', function($subQ) use ($user) {
                        $subQ->where('users.id', $user->id);
                    });
                });
            }
            // Admins and sub-admins see all portfolio items (no additional filtering)

            // Apply filters
            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('task_type_id')) {
                $query->whereHas('task', function ($q) use ($request) {
                    $q->where('task_type_id', $request->task_type_id);
                });
            }

            if ($request->has('deliverable_type')) {
                $query->where('deliverable_type', $request->deliverable_type);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $portfolios = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($portfolios, 'Portfolios retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving portfolios: ' . $e->getMessage());
        }
    }

    /**
     * Get client portfolio with filtering
     */
    public function clientPortfolio(Request $request, $clientId)
    {
        try {
            $client = Client::find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $query = Portfolio::with(['task', 'project', 'createdBy', 'taskType'])
                ->where('client_id', $clientId);

            // Apply filters
            if ($request->has('task_type_id')) {
                $query->whereHas('task', function ($q) use ($request) {
                    $q->where('task_type_id', $request->task_type_id);
                });
            }

            if ($request->has('deliverable_type')) {
                $query->where('deliverable_type', $request->deliverable_type);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $portfolios = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($portfolios, 'Client portfolio retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving client portfolio: ' . $e->getMessage());
        }
    }

    /**
     * Store a new portfolio item
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,id',
                'task_id' => 'nullable|exists:tasks,id',
                'project_id' => 'nullable|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'deliverable_type' => 'required|in:design,document,presentation,other',
                'status' => 'sometimes|in:completed,in_progress,review',
                'completed_at' => 'nullable|date',
                'media' => 'nullable|array',
                'media.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $portfolio = Portfolio::create([
                'client_id' => $request->client_id,
                'task_id' => $request->task_id,
                'project_id' => $request->project_id,
                'title' => $request->title,
                'description' => $request->description,
                'deliverable_type' => $request->deliverable_type,
                'status' => $request->get('status', 'completed'),
                'completed_at' => $request->completed_at,
                'created_by' => Auth::user()->id,
            ]);

            // Handle media uploads
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $portfolio->addMedia($file)
                        ->toMediaCollection('portfolio-media');
                }
            }

            DB::commit();

            $portfolio->load(['client', 'task', 'project', 'createdBy', 'taskType']);

            return $this->sendResponse($portfolio, 'Portfolio item created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error creating portfolio item: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific portfolio item
     */
    public function show($id)
    {
        try {
            $portfolio = Portfolio::with(['client', 'task', 'project', 'createdBy', 'taskType'])
                ->find($id);

            if (!$portfolio) {
                return $this->sendNotFound('Portfolio item not found');
            }

            // Load media
            $portfolio->loadMedia('portfolio-media');

            return $this->sendResponse($portfolio, 'Portfolio item retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving portfolio item: ' . $e->getMessage());
        }
    }

    /**
     * Update a portfolio item
     */
    public function update(Request $request, $id)
    {
        try {
            $portfolio = Portfolio::find($id);

            if (!$portfolio) {
                return $this->sendNotFound('Portfolio item not found');
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'deliverable_type' => 'sometimes|required|in:design,document,presentation,other',
                'status' => 'sometimes|in:completed,in_progress,review',
                'completed_at' => 'nullable|date',
                'media' => 'nullable|array',
                'media.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            DB::beginTransaction();

            $portfolio->update($request->only([
                'title', 'description', 'deliverable_type', 'status', 'completed_at'
            ]));

            // Handle media uploads
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $portfolio->addMedia($file)
                        ->toMediaCollection('portfolio-media');
                }
            }

            DB::commit();

            $portfolio->load(['client', 'task', 'project', 'createdBy', 'taskType']);
            $portfolio->loadMedia('portfolio-media');

            return $this->sendResponse($portfolio, 'Portfolio item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendServerError('Error updating portfolio item: ' . $e->getMessage());
        }
    }

    /**
     * Delete a portfolio item
     */
    public function destroy($id)
    {
        try {
            $portfolio = Portfolio::find($id);

            if (!$portfolio) {
                return $this->sendNotFound('Portfolio item not found');
            }

            // Delete associated media
            $portfolio->clearMediaCollection('portfolio-media');

            $portfolio->delete();

            return $this->sendResponse(null, 'Portfolio item deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting portfolio item: ' . $e->getMessage());
        }
    }

    /**
     * Upload media for a portfolio item
     */
    public function uploadMedia(Request $request, $id)
    {
        try {
            $portfolio = Portfolio::find($id);

            if (!$portfolio) {
                return $this->sendNotFound('Portfolio item not found');
            }

            $validator = Validator::make($request->all(), [
                'files' => 'required|array',
                'files.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $uploadedMedia = [];
            foreach ($request->file('files') as $file) {
                $media = $portfolio->addMedia($file)
                    ->toMediaCollection('portfolio-media');
                $uploadedMedia[] = $media;
            }

            return $this->sendResponse($uploadedMedia, 'Media uploaded successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error uploading media: ' . $e->getMessage());
        }
    }

    /**
     * Delete media from a portfolio item
     */
    public function deleteMedia($portfolioId, $mediaId)
    {
        try {
            $portfolio = Portfolio::find($portfolioId);

            if (!$portfolio) {
                return $this->sendNotFound('Portfolio item not found');
            }

            $media = $portfolio->getMedia('portfolio-media')->find($mediaId);

            if (!$media) {
                return $this->sendNotFound('Media not found');
            }

            $media->delete();

            return $this->sendResponse(null, 'Media deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting media: ' . $e->getMessage());
        }
    }

    /**
     * Get portfolio statistics for a client
     */
    public function clientStats($clientId)
    {
        try {
            $client = Client::find($clientId);
            if (!$client) {
                return $this->sendNotFound('Client not found');
            }

            $stats = [
                'total_items' => Portfolio::where('client_id', $clientId)->count(),
                'by_type' => Portfolio::where('client_id', $clientId)
                    ->selectRaw('deliverable_type, count(*) as count')
                    ->groupBy('deliverable_type')
                    ->pluck('count', 'deliverable_type'),
                'by_status' => Portfolio::where('client_id', $clientId)
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'recent_items' => Portfolio::where('client_id', $clientId)
                    ->with(['task', 'project'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            return $this->sendResponse($stats, 'Portfolio statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving portfolio statistics: ' . $e->getMessage());
        }
    }
}
