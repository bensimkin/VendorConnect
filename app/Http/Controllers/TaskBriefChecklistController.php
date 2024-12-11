<?php

namespace App\Http\Controllers;

use App\Models\TaskBriefChecklist;
use App\Services\DeletionService;
use Illuminate\Http\Request;

class TaskBriefChecklistController extends Controller
{
    public function store(Request $request)
    {
        //  $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'template_name' => ['required'],
            'check_brief' => 'required|array|min:1', // Ensure check_brief is an array and has at least 1 item
            'check_brief.*' => 'required|string', // Each item in the array must be a string and required
        ]);
        $formFields['task_brief_templates_id'] = $request->template_name;
        $formFields['checklist'] = json_encode($request->check_brief);

        if ($status = TaskBriefChecklist::create($formFields)) {
            return response()->json(['error' => false, 'message' => 'Task Brief checklist created successfully.', 'id' => $status->id, 'status' => $status]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief checklist Status couldn\'t created.']);
        }
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        // $taskBriefTemplate = TaskBriefTemplates::orderBy($sort, $order); // or 'desc'
        $task_checklist = TaskBriefChecklist::leftJoin('task_brief_templates','task_brief_templates.id', '=', 'task_brief_checklists.task_brief_templates_id')->select('task_brief_checklists.*','task_brief_templates.template_name as template_name')
        ->orderBy($sort, $order);
        if ($search) {
            $task_checklist = $task_checklist->where(function ($query) use ($search) {
                $query->where('checklist_text', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $task_checklist->count();
        $permissions = session()->get('permissions');
        $task_checklist = $task_checklist
            ->paginate(request("limit"))
            ->through(
                fn ($task_checklist) => [
                    'id' => $task_checklist->id,
                    'template_name'=> $task_checklist->template_name,
                    'checklist' => $task_checklist->checklist,
                    'actions'=>  (in_array('edit_task_brief_question', $permissions) ?
                    '<a href="javascript:void(0);" class="edit-brief-checklist" data-bs-toggle="modal" data-bs-target="#edit_tag_modal" data-id=' . $task_checklist->id . ' title="update" class="card-link"><i class="bx bx-edit mx-1"></i></a>' : "") .

                    (in_array('delete_task_brief_question', $permissions) ? '<button title="Delete" type="button" class="btn delete" data-id=' . $task_checklist->id . ' data-type="tags">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>' .
                    '</button>' : ""),
                    ]
            );
        return response()->json([
            "rows" => $task_checklist->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $Tasktemplateschecklist = TaskBriefChecklist::findOrFail($id);
        return response()->json(['task_templateschecklist' => $Tasktemplateschecklist]);
    }
    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'template_name' => ['required'],
            'check_brief' => 'required|array|min:1', // Ensure check_brief is an array and has at least 1 item
            'check_brief.*' => 'required|string', // Each item in the array must be a string and required
        ]);
        $formFields['task_brief_templates_id'] = $request->template_name;
        $formFields['checklist'] = json_encode($request->check_brief);
        $taskBrieftemplate = TaskBriefChecklist::findOrFail($request->id);
        if ($taskBrieftemplate->update($formFields)) {
            return response()->json(['error' => false, 'message' => 'Task Brief checklist Updated successfully.', 'id' => $taskBrieftemplate->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief checklist couldn\'t updated.']);
        }
    }
    public function destroy($id)
    {
        $taskBriefTemplate = TaskBriefChecklist::findOrFail($id);
        if ($taskBriefTemplate) {
            $response = DeletionService::delete(TaskBriefChecklist::class, $id, 'Task Brief checklist Template');
            return $response;
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief checklist Template can\'t be']);
        }
    }
}
