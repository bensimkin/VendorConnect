<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TagController extends BaseController
{
    /**
     * Get all tags
     */
    public function index(Request $request)
    {
        try {
            $query = Tag::query();
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tags = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($tags, 'Tags retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving tags: ' . $e->getMessage());
        }
    }

    /**
     * Store a new tag
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255|unique:tags,title',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $tag = Tag::create([
                'title' => $request->title,
                'slug' => \Str::slug($request->title),
            ]);

            return $this->sendResponse($tag, 'Tag created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating tag: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific tag
     */
    public function show($id)
    {
        try {
            $tag = Tag::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$tag) {
                return $this->sendNotFound('Tag not found');
            }

            return $this->sendResponse($tag, 'Tag retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving tag: ' . $e->getMessage());
        }
    }

    /**
     * Update a tag
     */
    public function update(Request $request, $id)
    {
        try {
            $tag = Tag::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$tag) {
                return $this->sendNotFound('Tag not found');
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:tags,name,' . $id . ',id,workspace_id,' . Auth::user()->workspace_id,
                'description' => 'nullable|string',
                'color' => 'nullable|string|max:7',
                'status' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $tag->update($request->only(['name', 'description', 'color', 'status']));

            return $this->sendResponse($tag, 'Tag updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating tag: ' . $e->getMessage());
        }
    }

    /**
     * Delete a tag
     */
    public function destroy($id)
    {
        try {
            $tag = Tag::where('workspace_id', Auth::user()->workspace_id)->find($id);

            if (!$tag) {
                return $this->sendNotFound('Tag not found');
            }

            $tag->delete();

            return $this->sendResponse(null, 'Tag deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting tag: ' . $e->getMessage());
        }
    }
}
