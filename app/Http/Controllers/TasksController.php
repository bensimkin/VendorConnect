<?php

namespace App\Http\Controllers;

use App\Mail\TaskAssignedMail;
use App\Models\ChecklistAnswered;
use App\Models\ChMessage;
use App\Models\QuestionAnswered;
use PDO;
use Exception;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;

use App\Models\Project;
use App\Models\Priority;
use App\Models\Workspace;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\UserClientPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TasksController extends Controller
{
    protected $workspace;
    protected $user;
    // public function __construct()
    // {
    //     $this->middleware(function ($request, $next) {
    //         // fetch session and use it in entire class with constructor
    //         $this->workspace = Workspace::find(session()->get('workspace_id'));
    //         $this->user = getAuthenticatedUser();
    //         return $next($request);
    //     });
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = '')
    {
        // dd($tasks->count());
        $project = (object)[];
        if ($id) {
            $project = Project::findOrFail($id);
            $tasks = $project->tasks;
            $toSelectTaskUsers = $project->users;
        } else {
            $toSelectTaskUsers = $this->workspace->users;
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks();
        }
        $tasks = $tasks->count();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
        return view('tasks.tasks', ['project' => $project, 'tasks' => $tasks, 'users' => $users, 'clients' => $clients, 'projects' => $projects, 'toSelectTaskUsers' => $toSelectTaskUsers]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = '')
    {
        $project = (object)[];
        $projects = [];
        if ($id) {
            $project = Project::find($id);
            $users = $project->users;
        } else {
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
            $users = $this->workspace->users;
        }
        $statuses = Status::where('admin_id', getAdminIdByUserRole())

            ->get();
        return view('tasks.create_task', ['project' => $project, 'projects' => $projects, 'users' => $users, 'statuses' => $statuses]);
    }

    public function TaskAnswerSubmit(Request $request)
    {
        $answer_by = Auth::user()->id;
        $formFields = $request->validate([
            'question_answer' => ['required'],
        ]);

        $findOrNot = QuestionAnswered::where('task_id', $request->input('task_id'))
            ->where('question_id', $request->input('question_id'))->first();

        $formFields['task_id'] = $request->input('task_id');
        $formFields['check_brief'] = ($request->input('check_brief') == 'on') ? 1 : 0;
        $formFields['question_id'] = $request->input('question_id');
        $formFields['answer_by'] = $answer_by;
        if ($findOrNot) {
            $id = $findOrNot->id;
            QuestionAnswered::where('id', $id)->update([
                'question_answer' => $formFields['question_answer'],
                'check_brief' => $formFields['check_brief']
            ]);
        } else {
            $answered = QuestionAnswered::create($formFields);
            $id = $answered->id;
        }
        Session::flash('message', 'Answered submit successfully.');
        return response()->json(['error' => false, 'message' => 'Answered submit successfully.', 'id' => $id]);
    }

    public function TaskCheckListAnswer(Request $request)
    {
        $answer_by = Auth::user()->id;
        $formFields = $request->validate([
            'check_brief' => 'required|array|min:1', // Ensure check_brief is an array and has at least 1 item
            'check_brief.*' => 'required|string', // Each item in the array must be a string and required
        ]);

        $findOrNot = ChecklistAnswered::where('task_id', $request->input('task_id'))
            ->where('checklist_id', $request->input('checklist_id'))->first();

        $formFields['task_id'] = $request->input('task_id');
        $formFields['checklist_id'] = $request->input('checklist_id');
        $formFields['answer_by'] = $answer_by;
        $formFields['checklist_answer'] = json_encode($formFields['check_brief']);
        if ($findOrNot) {
            $id = $findOrNot->id;
            ChecklistAnswered::where('id', $id)->update([
                'checklist_answer' => json_encode($formFields['check_brief']),
            ]);
        } else {
            $answered = ChecklistAnswered::create($formFields);
            $id = $answered->id;
        }
        Session::flash('message', 'Answered submit successfully.');
        return response()->json(['error' => false, 'message' => 'Answered submit successfully.', 'id' => $id]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $activeUser = Auth::user();

        $adminId = $activeUser->id;
        $formFields = $request->validate([
            'title' => ['required'],
            'status_id' => ['required', 'exists:statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'task_type_id' => ['required', 'exists:task_types,id'],
            'close_deadline' => ['required'],
            'start_date' => ['required'],
            'end_date' => ['required', 'after:start_date'],
            'description' => ['required'],
            'note' => ['nullable'],
        ]);


        $userIds = $request->input('users_id');
        $clientIds = $request->input('client_id');

        // $project_id = $request->input('project');
        // $formFields['status'] = Str::slug($request->input('status'));
        // $formFields['priority'] = Str::slug($request->input('priority'));
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
        $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');
        $formFields['admin_id'] = $adminId;
        $formFields['created_by'] = $adminId;

        $new_task = Task::create($formFields);
        $task_id = $new_task->id;
        $task = Task::find($task_id);
        $task->taskUsers()->attach($userIds);
        $task->taskClients()->attach($clientIds);
        // Prepare the notification data
        $notification_data = [
            'type' => 'task',
            'type_id' => $task_id,
            'type_title' => $task->title,
            'access_url' => 'tasks/information/' . $task->id,
            'action' => 'assigned',
            'title' => 'New task assigned',
            'message' => auth()->user()->first_name . ' ' . auth()->user()->last_name . ' assigned you a new task: ' . $task->title . ', ID #' . $task_id . '.'
        ];
        // Fetch users by their IDs and send the email
        $users = User::whereIn('id', $userIds)->get();
        foreach ($users as $user) {
            if ($user->id == auth()->user()->id) {
                continue;
            }
            // Mail::to($user->email)->send(new TaskAssignedMail($notification_data));
        }
        // $recipients = array_map(function ($userId) {
        //     return 'u_' . $userId;
        // }, $userIds);
        // processNotifications($notification_data, $recipients);
        Session::flash('message', 'Task created successfully.');
        return response()->json(['error' => false, 'message' => 'Task created successfully.', 'id' => $new_task->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::findOrFail($id);
        return view('tasks.task_information', ['task' => $task, 'auth_user' => $this->user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);
        // $project = $task->project;
        $users = $task->project->users;
        $task_users = $task->users;
        // $statuses = Status::where("admin_id", getAdminIdByUserRole())->get();
        // $tags = Tag::where("admin_id", getAdminIdByUserRole())->get();
        $statuses = Status::all();
        $tags = Tag::all();
        return view('tasks.update_task', ["task" => $task, "users" => $users, "task_users" => $task_users, 'statuses' => $statuses, 'tags' => $tags]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request->all());
        $formFields = $request->validate([
            'title' => ['required'],
            'status_id' => ['required', 'exists:statuses,id'],
            'priority_id' => ['required', 'exists:priorities,id'],
            'task_type_id' => ['required', 'exists:task_types,id'],
            'close_deadline' => ['required'],
            'start_date' => ['required'],
            'end_date' => ['required', 'after:start_date'],
            'description' => ['required'],
            'note' => ['nullable'],
        ]);
        $status = Status::findOrFail($request->input('status_id'));
        $id = $request->input('id');
        $task = Task::findOrFail($id);
        $currentStatusId = $task->status_id;

        // Check if the status has changed
        if ($currentStatusId != $request->input('status_id')) {
            $status = Status::findOrFail($request->input('status_id'));
            // if (!canSetStatus($status)) {
            //     return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
            // }
        }
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
        $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');

        $userIds = $request->input('users_id');
        $clientIds = $request->input('client_id');

        $task = Task::findOrFail($id);
        $task->update($formFields);

        // Get the current users associated with the task
        // $currentUsers = $task->taskUsers->pluck('id')->toArray();
        // $currentClients = $task->taskClients->pluck('id')->toArray();
        // $currentClients = $task->project->clients->pluck('id')->toArray();

        // Sync the users for the task
        $task->taskUsers()->sync($userIds);
        $task->taskClients()->sync($clientIds);
        // Get the new users associated with the task
        // $newUsers = array_diff($userIds, $currentUsers);

        // // Prepare notification data for new users
        // $notification_data = [
        //     'type' => 'task',
        //     'type_id' => $id,
        //     'type_title' => $task->title,
        //     'access_url' => 'tasks/information/' . $task->id,
        //     'action' => 'assigned',
        //     'title' => 'Task updated',
        //     'message' => $this->user->first_name . ' ' . $this->user->last_name . ' assigned you new task : ' . $task->title . ', ID #' . $id . '.'
        // ];

        // // Notify only the new users
        // $recipients = array_map(function ($userId) {
        //     return 'u_' . $userId;
        // }, $newUsers);

        // // Process notifications for new users
        // processNotifications($notification_data, $recipients);

        // if ($currentStatusId != $request->input('status_id')) {
        //     $currentStatus = Status::findOrFail($currentStatusId);
        //     $newStatus = Status::findOrFail($request->input('status_id'));

        //     $notification_data = [
        //         'type' => 'task_status_updation',
        //         'type_id' => $id,
        //         'type_title' => $task->title,
        //         'updater_first_name' => $this->user->first_name,
        //         'updater_last_name' => $this->user->last_name,
        //         'old_status' => $currentStatus->title,
        //         'new_status' => $newStatus->title,
        //         'access_url' => 'tasks/information/' . $id,
        //         'action' => 'status_updated',
        //         'title' => 'Task status updated',
        //         'message' => $this->user->first_name . ' ' . $this->user->last_name . ' has updated the status of task : ' . $task->title . ', ID #' . $id . ' from ' . $currentStatus->title . ' to ' . $newStatus->title
        //     ];

        //     $currentRecipients = array_merge(
        //         array_map(function ($userId) {
        //             return 'u_' . $userId;
        //         }, $currentUsers),
        //         array_map(function ($clientId) {
        //             return 'c_' . $clientId;
        //         }, $currentClients)
        //     );
        //     processNotifications($notification_data, $currentRecipients);
        // }


        return response()->json(['error' => false, 'id' => $id,  'message' => 'Task updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::find($id);
        DeletionService::delete(Task::class, $id, 'Task');
        return response()->json(['error' => false, 'message' => 'Task deleted successfully.', 'id' => $id, 'title' => $task->title, 'parent_id' => $task->project_id, 'parent_type' => 'project']);
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:tasks,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedTasks = [];
        $deletedTaskTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $task = Task::find($id);
            if ($task) {
                $deletedTaskTitles[] = $task->title;
                DeletionService::delete(Task::class, $id, 'Task');
                $deletedTasks[] = $id;
                $parentIds[] = $task->project_id;
            }
        }

        return response()->json(['error' => false, 'message' => 'Task(s) deleted successfully.', 'id' => $deletedTasks, 'titles' => $deletedTaskTitles, 'parent_id' => $parentIds, 'parent_type' => 'project']);
    }

    public function list($id = '')
    {

        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $status_ids = request('status_ids', []);
        $priority_ids = request('priority_ids', []);
        $user_ids = request('user_ids', []);

        $tasks = Task::whereNotNull('admin_id');

        if (!empty($user_ids)) {
            $taskIds = DB::table('task_user')
                ->whereIn('user_id', $user_ids)
                ->pluck('task_id')
                ->toArray();

            $tasks = $tasks->whereIn('id', $taskIds);
        }

        $activeUser = Auth::user();

        if ($activeUser->hasRole('Requester')) {
            // $taskIds = DB::table('client_task')
            // ->whereIn('client_id', [Auth::user()->client_id])
            // ->pluck('task_id')
            // ->toArray();

            // $tasks = $tasks->whereIn('id', $taskIds);

            $taskIds = DB::table('task_user')
                ->whereIn('user_id', [Auth::user()->id])
                ->pluck('task_id')
                ->toArray();

            $tasks = $tasks->whereIn('id', $taskIds)->orWhere('admin_id', Auth::user()->id);
        } else if ($activeUser->hasRole('Tasker')) {
            $taskIds = DB::table('task_user')
                ->whereIn('user_id', [Auth::user()->id])
                ->pluck('task_id')
                ->toArray();

            $tasks = $tasks->whereIn('id', $taskIds);
        }

        if ($search) {
            $tasks = $tasks->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($status_ids)) {
            $tasks = $tasks->whereIn('status_id', $status_ids);
        }

        if (!empty($priority_ids)) {
            $tasks = $tasks->whereIn('priority_id', $priority_ids);
        }

        $totaltasks = $tasks->count();

        $statuses = Status::all();
        $priorities = Priority::all();

        $tasks = $tasks->orderBy($sort, $order)
            ->paginate(request('limit'))
            ->through(function ($task) use ($priorities, $statuses) {
                // $statusOptions = '';
                // foreach ($statuses as $status) {
                //     // $disabled = canSetStatus($status) ? '' : 'disabled';
                //     $selected = $task->status_id == $status->id ? 'selected' : '';
                //     // {$disabled}
                //     $statusOptions .= "<option value='{$status->id}' class='badge bg-label-warning' {$selected} >{$status->title}</option>";
                // }

                // $priorityOptions = '';
                // foreach ($priorities as $priority) {
                //     $selectedPriority = $task->priority_id == $priority->id ? 'selected' : '';
                //     $priorityOptions .= "<option value='{$priority->id}' class='badge bg-label-{$priority->color}' {$selectedPriority}>{$priority->title}</option>";
                // }
                $permissions = session()->get('permissions');

                $actions = '';
                if (in_array('edit_tasks', $permissions)) {
                    $actions .= '<a href="javascript:void(0);" class="edit-task" data-id="' . $task->id . '" title="' . get_label('update', 'Update') . '">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>';
                }

                if (in_array('delete_tasks', $permissions)) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $task->id . '" data-type="tasks" data-table="task_table">' .
                        '<i class="bx bx-trash text-danger mx-1"></i>' .
                        '</button>';
                }
                $actions = $actions ?: '-';

                $userHtml = '';
                if (!empty($task->users) && count($task->users) > 0) {
                    $userHtml .= '<ul class="list-unstyled users-list m-0 d-flex avatar-group align-items-center">';
                    foreach ($task->users as $user) {
                        $userHtml .= "<li class='avatar avatar-sm pull-up'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' title='" . $user->first_name . ' ' . $user->last_name . "' /></li>";
                    }
                    // $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients" data-id="' . $task->id . '"><span class="bx bx-edit"></span></a></li>';
                    $userHtml .= '</ul>';
                } else {
                    $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients" data-id="' . $task->id . '">' .
                        '<span class="bx bx-edit"></span>' .
                        '</a>';
                }

                $clientHtml = '';
                if (!empty($task->taskClients) && count($task->taskClients) > 0) {
                    $clientHtml .= '<ul class="list-unstyled users-list m-0 d-flex avatar-group align-items-center">';
                    foreach ($task->taskClients as $client) {
                        $clientHtml .= "<li class='avatar avatar-sm pull-up'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' title='" . $client->first_name . " " . $user->last_name . "'/></li>";
                    }
                    $clientHtml .= '</ul>';
                }
                // Check if the task is expired
                $deadline = \Carbon\Carbon::parse($task->end_date);
                $current_time = now();
                $is_expire = ($current_time > $deadline) ? 'EXPIRED' : '';

                // Check if the task is expired or the deadline is closed and assign "Rejected" status
                $statusTitle = $task->status->title;
                if ($is_expire == 'EXPIRED' && $task->close_deadline == 1 ) {
                    $statusTitle = 'Rejected';
                }
                return [
                    'id' => $task->id,
                    'title' => "<a href='" . route('task.information.view', $task->id) . "' target='_blank' title='" . strip_tags($task->description) . "'><strong>{$task->title}</strong></a>",
                    'users' => $userHtml,
                    'clients' => $clientHtml,
                    'start_date' => format_date($task->start_date),

                    'close_deadline' => $task->close_deadline == 0 ? '<span class="badge bg-danger">No</span>' : '<span class="badge bg-primary">Yes</span>',
                    // 'status' => ucwords(str_replace('-', ' ', $task->status)),
                    // 'status_id' => $task->status->title,
                    'status_id' => $statusTitle,
                    'priority_id' => $task->priority->title,
                    // 'priority' => ucwords(str_replace('-', ' ', $task->priority)),
                    // 'created_at' => format_date($task->created_at, true),
                    // 'updated_at' => format_date($task->updated_at, true),
                    'actions' => $actions
                ];
            });

        return response()->json([
            "rows" => $tasks->items(),
            "total" => $totaltasks,
        ]);
    }

    public function dragula($id = '')
    {
        $project = (object)[];
        $projects = [];
        if ($id) {
            $project = Project::findOrFail($id);
            $tasks = isAdminOrHasAllDataAccess() ? $project->tasks : $this->user->project_tasks($id);
            $toSelectTaskUsers = $project->users;
        } else {
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
            $toSelectTaskUsers = $this->workspace->users;
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks()->get();
        }
        if (request()->has('status')) {
            $tasks = $tasks->where('status_id', request()->status);
        }
        if (request()->has('project')) {
            $project = Project::findOrFail(request()->project);
            $tasks = $tasks->where('project_id', request()->project);
            $toSelectTaskUsers = $project->users;
        }
        $total_tasks = $tasks->count();
        return view('tasks.board_view', ['project' => $project, 'tasks' => $tasks, 'total_tasks' => $total_tasks, 'projects' => $projects, 'toSelectTaskUsers' => $toSelectTaskUsers]);
    }

    public function updateStatus(Request $request)
    {
        $task = Task::findOrFail($request->task_id);
        $task->status_id = $request->newStatus;
        if ($task->save()) {
            return response()->json(['error' => false, 'message' => 'Task status updated successfully.', 'id' => $request->task_id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task status couldn\'t updated.']);
        }
    }

    public function updatedeadline(Request $request)
    {
        $task = Task::findOrFail($request->task_id);
        $task->close_deadline = $request->close_deadline;
        if ($task->save()) {
            return response()->json(['error' => false, 'message' => 'Task deadline updated successfully.', 'id' => $request->task_id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Task deadline couldn\'t updated.']);
        }
    }



    public function duplicate($id)
    {
        // Define the related tables for this meeting
        $relatedTables = ['users']; // Include related tables as needed

        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Task::class, $id, $relatedTables, $title);

        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Task duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Task duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Task duplicated successfully.', 'id' => $id, 'parent_id' => $duplicate->project->id, 'parent_type' => 'project']);
    }

    public function upload_media(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'id' => 'integer|exists:tasks,id'
            ]);

            $mediaIds = [];

            if ($request->hasFile('media_files')) {
                $task = Task::find($validatedData['id']);
                $mediaFiles = $request->file('media_files');

                foreach ($mediaFiles as $mediaFile) {
                    $mediaItem = $task->addMedia($mediaFile)
                        ->sanitizingFileName(function ($fileName) {
                            // Replace special characters and spaces with hyphens
                            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        })
                        ->toMediaCollection('task-media');

                    $mediaIds[] = $mediaItem->id;
                }

                Session::flash('message', 'File(s) uploaded successfully.');
                return response()->json(['error' => false, 'message' => 'File(s) uploaded successfully.', 'id' => $mediaIds, 'type' => 'media']);
            } else {
                Session::flash('error', 'No file(s) chosen.');
                return response()->json(['error' => true, 'message' => 'No file(s) chosen.']);
            }
        } catch (Exception $e) {
            // Handle the exception as needed
            Session::flash('error', 'An error occurred during file upload: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'An error occurred during file upload: ' . $e->getMessage()]);
        }
    }


    public function get_media($id)
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $task = Task::findOrFail($id);
        $media = $task->getMedia('task-media');

        if ($search) {
            $media = $media->filter(function ($mediaItem) use ($search) {
                return (
                    // Check if ID contains the search query
                    stripos($mediaItem->id, $search) !== false ||
                    // Check if file name contains the search query
                    stripos($mediaItem->file_name, $search) !== false ||
                    // Check if date created contains the search query
                    stripos($mediaItem->created_at->format('Y-m-d'), $search) !== false
                );
            });
        }


        $formattedMedia = $media->map(function ($mediaItem) {
            // Check if the disk is public
            $isPublicDisk = $mediaItem->disk == 'public' ? 1 : 0;

            // Generate file URL based on disk visibility
            $fileUrl = $isPublicDisk
                ? asset('storage/task-media/' . $mediaItem->file_name)
                : $mediaItem->getFullUrl();
            return [
                'id' => $mediaItem->id,
                'file' => '<a href="' . $fileUrl . '" data-lightbox="task-media"> <img src="' . $fileUrl . '" alt="' . $mediaItem->file_name . '" width="50"></a>',
                'file_name' => $mediaItem->file_name,
                'file_size' => formatSize($mediaItem->size),
                'created_at' => format_date($mediaItem->created_at),
                'updated_at' => format_date($mediaItem->updated_at),
                'actions' => [
                    '<a href="' . $fileUrl . '" title="' . get_label('download', 'Download') . '" download>' .
                        '<i class="bx bx-download bx-sm"></i>' .
                        '</a>' .
                        '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $mediaItem->id . '" data-type="task-media" data-table = "task_media_table" >' .
                        '<i class="bx bx-trash text-danger"></i>' .
                        '</button>'
                ],


            ];
        });

        if ($order == 'asc') {
            $formattedMedia = $formattedMedia->sortBy($sort);
        } else {
            $formattedMedia = $formattedMedia->sortByDesc($sort);
        }

        return response()->json([
            'rows' => $formattedMedia->values()->toArray(),
            'total' => $formattedMedia->count(),
        ]);
    }

    public function delete_media($mediaId)
    {
        $mediaItem = Media::find($mediaId);

        if (!$mediaItem) {
            // Handle case where media item is not found
            return response()->json(['error' => true, 'message' => 'File not found.']);
        }

        // Delete media item from the database and disk
        $mediaItem->delete();

        return response()->json(['error' => false, 'message' => 'File deleted successfully.', 'id' => $mediaId, 'title' => $mediaItem->file_name, 'parent_id' => $mediaItem->model_id,  'type' => 'media', 'parent_type' => 'task']);
    }

    public function delete_multiple_media(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:media,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $media = Media::find($id);
            if ($media) {
                $deletedIds[] = $id;
                $deletedTitles[] = $media->file_name;
                $parentIds[] = $media->model_id;
                $media->delete();
            }
        }

        return response()->json(['error' => false, 'message' => 'Files(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'parent_id' => $parentIds, 'type' => 'media', 'parent_type' => 'task']);
    }

    public function get_message($id)
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $task = Task::findOrFail($id);
        $message = $task->messages;

        $formattedMessage = $message->map(function ($messageItem) {
            $fullnameGet = User::where('id', $messageItem->sender_id)->first();
            $fullname = $fullnameGet->first_name . ' ' . $fullnameGet->last_name;
            return [
                // 'id' => $messageItem->id,
                'sender_id' => $fullname,
                'message_text' => $messageItem->message_text,
                'sent_at' => Carbon::parse($messageItem->seent_at)->format('F j, Y, g:i A'),
                'created_at' => format_date($messageItem->created_at),
                'updated_at' => format_date($messageItem->updated_at),
                'actions' => [
                    '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $messageItem->id . '" data-type="task-message" data-table = "ch_messages" >' .
                        '<i class="bx bx-trash text-danger"></i>' .
                        '</button>'
                ],
            ];
        });

        return response()->json([
            'rows' => $formattedMessage->values()->toArray(),
            'total' => $formattedMessage->count(),
        ]);
    }

    public function upload_message(Request $request)
    {

        try {
            $validatedData = $request->validate([
                'id' => 'integer|exists:tasks,id',
                'message_text' => ['required']
            ]);

            $messageIds = [];

            $task = Task::find($validatedData['id']);
            $message_text = $request->input('message_text');
            ChMessage::create([
                'task_id' =>  $request->input('id'),
                'sender_id' =>  Auth::user()->id,
                'message_text' => $message_text,
            ]);

            Session::flash('message', 'Message uploaded successfully.');
            return response()->json(['error' => false, 'message' => 'Message uploaded successfully.', 'id' => $messageIds, 'type' => 'message']);
        } catch (Exception $e) {
            // Handle the exception as needed
            Session::flash('error', 'An error occurred during message upload: ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'An error occurred during message upload: ' . $e->getMessage()]);
        }
    }

    public function delete_message($messageId)
    {
        $messageItem = ChMessage::find($messageId);

        if (!$messageItem) {
            // Handle case where message item is not found
            return response()->json(['error' => true, 'message' => 'Message not found.']);
        }

        // Delete message item from the database and disk
        $messageItem->delete();

        return response()->json(['error' => false, 'message' => 'Message deleted successfully.', 'id' => $messageId,  'type' => 'message', 'parent_type' => 'task']);
    }

    public function delete_multiple_message(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:ch_messages,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        foreach ($ids as $id) {
            $message = ChMessage::find($id);
            if ($message) {
                $deletedIds[] = $id;
                $message->delete();
            }
        }

        return response()->json(['error' => false, 'message' => 'Files(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'parent_id' => $parentIds, 'type' => 'message', 'parent_type' => 'task']);
    }


    public function get($id)
    {
        // $task = Task::with('users')->findOrFail($id);
        // $task_condition = $task->taskClients->firstOrFail();

        // return response()->json(['error' => false, 'task' => $task, 'task_condition' => $task_condition]);

        // $task = Task::with('users')->findOrFail($id);
        // $task_condition = $task->taskClients->firstOrFail();
        // $task_users = $task_condition->users;
        // return response()->json([
        //     'error' => false,
        //     'task' => $task,
        //     'task_condition' => $task_condition,
        //     'task_users' => $task_users
        // ]);

        $task = Task::with('users')->findOrFail($id);
        $task_condition = $task->taskClients;
        $task_users = $task->users;
        return response()->json([
            'error' => false,
            'task' => $task,
            'task_condition' => $task_condition,
            'task_users' => $task_users
        ]);
    }
    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        UserClientPreference::updateOrCreate(
            ['user_id' => $prefix . $this->user->id, 'table_name' => 'tasks'],
            ['default_view' => $view]
        );
        return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
    }
    public function update_priority(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'priorityId' => ['nullable']

        ]);
        $id = $request->id;
        $priorityId = $request->priorityId;
        $task = Task::findOrFail($id);
        $currentPriority = $task->priority ? $task->priority->title : 'Default';
        $task->priority_id = $priorityId;
        $task->note = $request->note;
        if ($task->save()) {
            // Reload the task to get updated priority information
            $task = $task->fresh();
            $newPriority = $task->priority ? $task->priority->title : 'Default';
            $message = $this->user->first_name . ' ' . $this->user->last_name . ' updated task priority from ' . $currentPriority . ' to ' . $newPriority;
            return response()->json(['error' => false, 'message' => 'Priority updated successfully.', 'id' => $id, 'type' => 'task', 'activity_message' => $message]);
        } else {
            return response()->json(['error' => true, 'message' => 'Priority couldn\'t updated.']);
        }
    }
    public function update_status(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'statusId' => ['required']

        ]);
        $id = $request->id;
        $statusId = $request->statusId;
        $status = Status::findOrFail($statusId);
        if (canSetStatus($status)) {
            $task = Task::findOrFail($id);
            $currentStatus = $task->status->title;
            $task->status_id = $statusId;
            $task->note = $request->note;
            if ($task->save()) {
                $task = $task->fresh();
                $newStatus = $task->status->title;

                $notification_data = [
                    'type' => 'task_status_updation',
                    'type_id' => $id,
                    'type_title' => $task->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $currentStatus,
                    'new_status' => $newStatus,
                    'access_url' => 'tasks/information/' . $id,
                    'action' => 'status_updated'
                ];
                $userIds = $task->users->pluck('id')->toArray();
                $clientIds = $task->project->clients->pluck('id')->toArray();
                $recipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $userIds),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $clientIds)
                );
                processNotifications($notification_data, $recipients);


                return response()->json(['error' => false, 'message' => 'Status updated successfully.', 'id' => $id, 'type' => 'task', 'activity_message' => $this->user->first_name . ' ' . $this->user->last_name . ' updated task status from ' . $currentStatus . ' to ' . $newStatus]);
            } else {
                return response()->json(['error' => true, 'message' => 'Status couldn\'t updated.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
        }
    }
}
