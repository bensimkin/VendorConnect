<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\TaskBriefQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskBriefQuestionController extends BaseController
{
    /**
     * Get all task brief questions
     */
    public function index(Request $request)
    {
        try {
            $query = TaskBriefQuestion::query();
            // Removed workspace filtering for single-tenant system

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('question', 'like', "%{$search}%");
            }

            if ($request->has('template_id')) {
                $query->where('task_brief_templates_id', $request->template_id);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $questions = $query->paginate($request->get('per_page', 15));

            return $this->sendPaginatedResponse($questions, 'Task brief questions retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task brief questions: ' . $e->getMessage());
        }
    }

    /**
     * Store a new task brief question
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template_id' => 'required|exists:task_brief_templates,id',
                'question' => 'required|string',
                'question_type' => 'required|string|in:text,textarea,select,checkbox,radio',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $question = TaskBriefQuestion::create([
                'task_brief_templates_id' => $request->template_id,
                'question_text' => $request->question,
                'question_type' => $request->question_type,
            ]);

            return $this->sendResponse($question, 'Task brief question created successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error creating task brief question: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific task brief question
     */
    public function show($id)
    {
        try {
            $question = TaskBriefQuestion::find($id);

            if (!$question) {
                return $this->sendNotFound('Task brief question not found');
            }

            return $this->sendResponse($question, 'Task brief question retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error retrieving task brief question: ' . $e->getMessage());
        }
    }

    /**
     * Update a task brief question
     */
    public function update(Request $request, $id)
    {
        try {
            $question = TaskBriefQuestion::find($id);

            if (!$question) {
                return $this->sendNotFound('Task brief question not found');
            }

            $validator = Validator::make($request->all(), [
                'template_id' => 'sometimes|required|exists:task_brief_templates,id',
                'question' => 'sometimes|required|string',
                'question_type' => 'sometimes|required|string|in:text,textarea,select,checkbox,radio',
            ]);

            if ($validator->fails()) {
                return $this->sendValidationError($validator->errors());
            }

            $validated = $request->only(['template_id', 'question', 'question_type']);
            if (isset($validated['template_id'])) {
                $validated['task_brief_templates_id'] = $validated['template_id'];
                unset($validated['template_id']);
            }
            if (isset($validated['question'])) {
                $validated['question_text'] = $validated['question'];
                unset($validated['question']);
            }
            $question->update($validated);

            return $this->sendResponse($question, 'Task brief question updated successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error updating task brief question: ' . $e->getMessage());
        }
    }

    /**
     * Delete a task brief question
     */
    public function destroy($id)
    {
        try {
            $question = TaskBriefQuestion::find($id);

            if (!$question) {
                return $this->sendNotFound('Task brief question not found');
            }

            $question->delete();

            return $this->sendResponse(null, 'Task brief question deleted successfully');
        } catch (\Exception $e) {
            return $this->sendServerError('Error deleting task brief question: ' . $e->getMessage());
        }
    }
}
