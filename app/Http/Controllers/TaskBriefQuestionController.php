<?php

namespace App\Http\Controllers;

use App\Models\QuestionAnswered;
use App\Models\TaskBriefQuestion;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TaskBriefQuestionController extends Controller
{
    public function store(Request $request)
    {
        //  $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'template_name' => ['required'],
            'questiondescription' => ['required'],
            'question_type' => ['required'],
        ]);
        $formFields['task_brief_templates_id'] = $request->template_name;
        $formFields['question_text'] = $request->questiondescription;
        $formFields['question_type'] = $request->question_type;

        if ($status = TaskBriefQuestion::create($formFields)) {

            // Default Answer when creating Template Questions
            $formFields['task_id'] = 0;
            $formFields['check_brief'] = 0;
            $formFields['question_id'] = $status->id;
            $formFields['answer_by'] = Auth::user()->id;
            $formFields['question_answer'] = $request->answer_description;

            $answered = QuestionAnswered::create($formFields);
            ////

            return response()->json(['error' => false, 'message' => 'Task Brief Information created successfully.', 'id' => $status->id, 'status' => $status]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief Information Status couldn\'t created.']);
        }
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        // $taskBriefTemplate = TaskBriefTemplates::orderBy($sort, $order); // or 'desc'
        $task_question = TaskBriefQuestion::leftJoin('task_brief_templates','task_brief_templates.id', '=', 'task_brief_questions.task_brief_templates_id')->select('task_brief_questions.*','task_brief_templates.template_name as template_name')
        ->orderBy($sort, $order);
        if ($search) {
            $task_question = $task_question->where(function ($query) use ($search) {
                $query->where('question_text', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $task_question->count();
        $permissions = session()->get('permissions');
        $task_question = $task_question
            ->paginate(request("limit"))
            ->through(
                fn ($task_question) => [
                    'id' => $task_question->id,
                    'template_name'=> $task_question->template_name,
                    'question_text' => $task_question->question_text,
                    'question_type' => $task_question->question_type == 1 ? 'Long Information' : 'Short Information',
                    'actions'=>  (in_array('edit_task_brief_question', $permissions) ?
                    '<a href="javascript:void(0);" class="edit-brief-question" data-bs-toggle="modal" data-bs-target="#edit_tag_modal" data-id=' . $task_question->id . ' title="update" class="card-link"><i class="bx bx-edit mx-1"></i></a>' : "") .

                    (in_array('delete_task_brief_question', $permissions) ? '<button title="Delete" type="button" class="btn delete" data-id=' . $task_question->id . ' data-type="tags">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>' .
                    '</button>' : ""),
                    ]
            );
        return response()->json([
            "rows" => $task_question->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $Tasktemplatesquestion = TaskBriefQuestion::with('questionAnswered')->findOrFail($id);
        return response()->json(['task_templatesquestion' => $Tasktemplatesquestion]);
    }
    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'update_template_name' => ['required'],
            'update_question' => ['required'],
            'update_question_type' => ['required'],
            'question_answer' => ['nullable'], // Optional answer update
        ]);
        $formFields['task_brief_templates_id'] = $request->update_template_name;
        $formFields['question_text'] = $request->update_question;
        $formFields['question_type'] = $request->update_question_type;
        $taskBrieftemplate = TaskBriefQuestion::findOrFail($request->id);

        if ($taskBrieftemplate->update($formFields)) {
            if($request->has('update_default_answer')) {
                $questionAnswered = QuestionAnswered::where('question_id', $request->id)->first();

            if ($questionAnswered) {
                // Update the answer if it exists
                $questionAnswered->update([
                    'question_answer' => $request->update_default_answer
                ]);
            }else{
                 // Default Answer when creating Template Questions
                $formFields['task_id'] = 0;
                $formFields['check_brief'] = 0;
                $formFields['question_id'] = $request->id;
                $formFields['answer_by'] = Auth::user()->id;
                $formFields['question_answer'] = $request->update_default_answer;

                $answered = QuestionAnswered::create($formFields);
            }
        }
            return response()->json(['error' => false, 'message' => 'Task Brief Question Updated successfully.', 'id' => $taskBrieftemplate->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief Question couldn\'t updated.']);
        }
    }
    public function destroy($id)
    {
        $taskBriefTemplate = TaskBriefQuestion::findOrFail($id);
        if ($taskBriefTemplate) {
            $response = DeletionService::delete(TaskBriefQuestion::class, $id, 'Task Brief Information Template');
            return $response;
        } else {
            return response()->json(['error' => true, 'message' => 'Task Brief question Information can\'t be']);
        }
    }
}
