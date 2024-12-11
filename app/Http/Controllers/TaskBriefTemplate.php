<?php

namespace App\Http\Controllers;

use App\Models\TaskBriefQuestion;
use App\Models\TaskBriefTemplates;
use App\Models\TaskType;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskBriefTemplate extends Controller
{

    public function store(Request $request)
    {
        //  $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'template_name' => ['required'],
            'task_template_id' => ['required'],
        ]);
        $formFields['template_name'] = $request->template_name;
        $formFields['task_type_id'] = $request->task_template_id;

        // $roleIds = $request->input('role_ids');
        if ($status = TaskBriefTemplates::create($formFields)) {
            // $status->roles()->attach($roleIds);
            return response()->json(['error' => false, 'message' => 'Task Brief Template created successfully.', 'id' => $status->id, 'status' => $status]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief Template Status couldn\'t created.']);
        }
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        // $taskBriefTemplate = TaskBriefTemplates::orderBy($sort, $order); // or 'desc'
        $taskBriefTemplates = TaskBriefTemplates::leftJoin('task_types', 'task_types.id', '=', 'task_brief_templates.task_type_id')
        ->select('task_brief_templates.*', 'task_types.task_type as task_type_name') // Select fields you need
        ->orderBy($sort, $order);
        $adminId = getAdminIdByUserRole();
        // $tags->where('admin_id', $adminId);
        if ($search) {
            $taskBriefTemplates = $taskBriefTemplates->where(function ($query) use ($search) {
                $query->where('template_name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $taskBriefTemplates->count();
        $permissions = session()->get('permissions');
        $taskBriefTemplates = $taskBriefTemplates
            ->paginate(request("limit"))
            ->through(
                fn ($task_type) => [
                    'id' => $task_type->id,
                    'task_type'=> $task_type->task_type_name,
                    'template_name' => $task_type->template_name,
                    'actions'=>  (in_array('edit_task_brief_templates', $permissions) ?
                    '<a href="javascript:void(0);" class="edit-brief-template" data-bs-toggle="modal" data-bs-target="#edit_tag_modal" data-id=' . $task_type->id . ' title="update" class="card-link"><i class="bx bx-edit mx-1"></i></a>' : "") .

                    (in_array('delete_task_brief_templates', $permissions) ? '<button title="Delete Task Type" type="button" class="btn delete" data-id=' . $task_type->id . ' data-type="tags">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>' : ""),
                ]
            );
        return response()->json([
            "rows" => $taskBriefTemplates->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $Tasktemplates = TaskBriefTemplates::findOrFail($id);
        return response()->json(['task_templates' => $Tasktemplates]);
    }
    public function update(Request $request)
    {
        $formFields = $request->validate([
            'template_id' => ['required'],
            'task_template_id' => ['required'],
        ]);
        $formFields['template_name'] = $request->template_name;
        $formFields['task_type_id'] = $request->task_template_id;
        // $formFields['slug'] = $slug;
        $taskBrieftemplate = TaskBriefTemplates::findOrFail($request->template_id);
        if ($taskBrieftemplate->update($formFields)) {
            return response()->json(['error' => false, 'message' => 'Task Brief Template Updated successfully.', 'id' => $taskBrieftemplate->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief Template couldn\'t updated.']);
        }
    }
    public function destroy($id)
    {
        $taskBriefTemplate = TaskBriefTemplates::findOrFail($id);
        $exist_task_conditon = TaskBriefQuestion::where('task_brief_templates_id',$id);
        if($exist_task_conditon->count() > 0){
            return response()->json(['error' => true, 'message' => 'Task Brief Template Name exists in Task Brief Questions and cannot be deleted.']);
        }
        if ($taskBriefTemplate) {
            $response = DeletionService::delete(TaskBriefTemplates::class, $id, 'Task Brief Template deleted');
            return $response;
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief Template can\'t be deleted.']);
        }
    }
}
