<?php

namespace App\Http\Controllers;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
class TagsController extends Controller
{
    public function index()
    {
        return view('tags.list');
    }
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'add_tags' => ['required'],
        ]);
        $slug = generateUniqueSlug($request->add_tags, Tag::class);
        $formFields['title'] = $request->add_tags;
        $formFields['slug'] = $slug;
        $formFields['admin_id'] = getAdminIdByUserRole();
        $tag = Tag::create($formFields);
        return response()->json(['error' => false, 'message' => 'Tag created successfully.', 'id' => $tag->id, 'tag' => $tag]);
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $tags = Tag::orderBy($sort, $order); // or 'desc'
        $adminId = getAdminIdByUserRole();
        $tags->where('admin_id', $adminId);
        if ($search) {
            $tags = $tags->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $tags->count();
        $permissions = session()->get('permissions');
        $tags = $tags
            ->paginate(request("limit"))
            ->through(
                fn ($tag) => [
                    'id' => $tag->id,
                    'tags' => $tag->title,
                    'actions'=>  (in_array('edit_tags', $permissions) ?
                    '<a href="javascript:void(0);" class="edit-tag" data-bs-toggle="modal" data-bs-target="#edit_tag_modal" data-id=' .$tag->id. ' title="update" class="card-link"><i class="bx bx-edit mx-1"></i></a>' : "") .

                    (in_array('delete_tags', $permissions) ? '<button title="Delete" type="button" class="btn delete" data-id=' .$tag->id. ' data-type="tags">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>' : ""),
                ]
            );
        return response()->json([
            "rows" => $tags->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $tag = Tag::findOrFail($id);
        return response()->json(['tag' => $tag]);
    }
    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'add_tag' => ['required'],
        ]);
        $slug = generateUniqueSlug($request->add_tag, Tag::class, $request->id);
        $formFields['title'] = $request->add_tag;
        $formFields['slug'] = $slug;
        $tag = Tag::findOrFail($request->id);
        if ($tag->update($formFields)) {
            return response()->json(['error' => false, 'message' => 'Tag updated successfully.', 'id' => $tag->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Tag couldn\'t updated.']);
        }
    }
    public function get_suggestions()
    {
        $tags = Tag::pluck('title');
        return response()->json($tags);
    }
    public function get_ids(Request $request)
    {
        $tagNames = $request->input('tag_names');
        $tagIds = Tag::whereIn('title', $tagNames)->pluck('id')->toArray();
        return response()->json(['tag_ids' => $tagIds]);
    }
    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        if ($tag->projects()->count() > 0) {
            return response()->json(['error' => true, 'message' => 'Tag can\'t be deleted.It is associated with a project']);
        } else {
            $response = DeletionService::delete(Tag::class, $id, 'Tag');
        return $response;
        }
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:tags,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $tag = Tag::findOrFail($id);
            if ($tag->projects()->count() > 0) {
                return response()->json(['error' => true, 'message' => 'Tag can\'t be deleted.It is associated with a project']);
            } else {
                $deletedIds[] = $id;
                $deletedTitles[] = $tag->title;
                DeletionService::delete(Tag::class, $id, 'Tag');
            }
        }
        return response()->json(['error' => false, 'message' => 'Tag(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }
}
