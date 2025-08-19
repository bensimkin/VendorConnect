<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskBriefTemplates;
use App\Models\TaskType;
use App\Services\DeletionService;
use Illuminate\Http\Request;

class TaskTypeController extends Controller
{
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'task_type' => ['required'],
        ]);
        // $slug = generateUniqueSlug($request->add_tags, Tag::class);
        $formFields['task_type'] = $request->task_type;
        // $formFields['slug'] = $slug;
        // $formFields['admin_id'] = getAdminIdByUserRole();
        $task_type = TaskType::create($formFields);
        return response()->json(['error' => false, 'message' => 'Task type created successfully.', 'id' => $task_type->id, 'task_type' => $task_type]);
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $taskType = TaskType::orderBy($sort, $order); // or 'desc'
        // $adminId = getAdminIdByUserRole();
        // $tags->where('admin_id', $adminId);
        if ($search) {
            $taskType = $taskType->where(function ($query) use ($search) {
                $query->where('task_type', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $taskType->count();

        // $editButton = ;
        // $deleteButton = ;
        $permissions = session()->get('permissions');

        $taskType = $taskType
            ->paginate(request("limit"))
            ->through(
                fn ($task_type) => [
                    'id' => $task_type->id,
                    'task_type' => $task_type->task_type,
                    'actions'=>  (in_array('edit_task_types', $permissions) ?
                    '<a href="javascript:void(0);" class="edit-tag" data-bs-toggle="modal" data-bs-target="#edit_tag_modal" data-id=' . $task_type->id . ' title="Edit Task Type" class="card-link"><i class="bx bx-edit mx-1"></i></a>' : "") .

                    (in_array('edit_task_types', $permissions) ? '<button title="Delete Task Type" type="button" class="btn delete" data-id=' . $task_type->id . ' data-type="tags">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>' : ""),
                ]
            );
        return response()->json([
            "rows" => $taskType->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $TaskType = TaskType::findOrFail($id);
        return response()->json(['task_types' => $TaskType]);
    }
    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'task_type' => ['required'],
        ]);
        // $slug = generateUniqueSlug($request->add_tag, TaskType::class, $request->id);
        $formFields['task_type'] = $request->task_type;
        // $formFields['slug'] = $slug;
        $task_type = TaskType::findOrFail($request->id);
        if ($task_type->update($formFields)) {
            return response()->json(['error' => false, 'message' => 'Task type Updated successfully.', 'id' => $task_type->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task type couldn\'t updated.']);
        }
    }
    // public function get_suggestions()
    // {
    //     $tags = Tag::pluck('title');
    //     return response()->json($tags);
    // }
    // public function get_ids(Request $request)
    // {
    //     $tagNames = $request->input('tag_names');
    //     $tagIds = Tag::whereIn('title', $tagNames)->pluck('id')->toArray();
    //     return response()->json(['tag_ids' => $tagIds]);
    // }
    public function destroy($id)
    {
        // $task_type = TaskType::findOrFail($id);
        // if ($task_type->projects()->count() > 0) {
        //     return response()->json(['error' => true, 'message' => 'Task Type can\'t be deleted.It is associated with a project']);
        // } else {
        //     $response = DeletionService::delete(TaskType::class, $id, 'task_type');
        // return $response;
        // }


        $task_type = TaskType::findOrFail($id);
        $task =  Task::where('task_type_id',$id);
        $task_brief_template =  TaskBriefTemplates::where('task_type_id',$id);
        if($task_brief_template->count() > 0 || $task->count() > 0){
            return response()->json(['error' => true, 'message' => 'Task Type exists in Task Brief Template or in Task module and cannot be deleted.']);
        }
        if ($task_type) {
            $response = DeletionService::delete(TaskType::class, $id, 'Task Type');
            return $response;
        } else {
            return response()->json(['error' => true, 'message' => 'Task Type can\'t be deleted.It is associated with a project']);
        }
    }
}
