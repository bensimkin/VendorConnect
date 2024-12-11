<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Priority;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskBriefTemplates;
use App\Models\TaskType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class FrontEndController extends Controller
{
    public function UserProfile(string $id)
    {
        $title = 'User Profile';
        $UserRoles = Role::where('guard_name', 'web')->get();
        $activeUser = Auth::user();
        return view('front-end.profile', compact('title', 'UserRoles', 'activeUser'));
    }
    public function DashboardView()
    {
        // dd(session()->get('user_id'));
        // dd(Auth::user());
        // dd(session()->get('permissions'));
        $title = 'Dashboard';
        $user = auth()->user();

        $taskTypeCount = TaskType::count();
        $taskType = TaskType::all();
        $statuses = Status::all();
        $priorities = priority::all();

        $taskTypeResponse = [];
        $statusesTypeResponse = [];
        $priorityResponse = [];

        if ($user->hasRole('Admin')) {
            $taskCount = Task::count();
            $usersCount = User::whereNotNull('client_id')->count();
            $clientCount = Client::count();


            foreach ($taskType as $type) {
                array_push($taskTypeResponse, [$type->task_type => Task::where('task_type_id', $type->id)->count()]);
            }

            foreach ($statuses as $status) {
                array_push($statusesTypeResponse, [$status->title => Task::where('status_id', $status->id)->count()]);
            }

            foreach ($priorities as $priority) {
                array_push($priorityResponse, [$priority->title => Task::where('priority_id', $priority->id)->count()]);
            }
        } else if ($user->hasRole('Tasker')) {
            $taskCount = $user->userTask()->count();
            $usersCount = 0; // Users related to the manager via tasks

            // Count clients related to the tasks associated with the user
            $clientCount = Client::whereHas('clientTasks', function ($query) use ($user) {
                $query->whereIn('task_id', $user->userTask()->pluck('tasks.id'));
            })->count();

            foreach ($taskType as $type) {
                array_push($taskTypeResponse, [$type->task_type => Task::where('task_type_id', $type->id)->whereIn('id', $user->userTask()->pluck('tasks.id'))->count()]);
            }

            foreach ($statuses as $status) {
                array_push($statusesTypeResponse, [$status->title => Task::where('status_id', $status->id)->whereIn('id', $user->userTask()->pluck('tasks.id'))->count()]);
            }

            foreach ($priorities as $priority) {
                array_push($priorityResponse, [$priority->title => Task::where('priority_id', $priority->id)->whereIn('id', $user->userTask()->pluck('tasks.id'))->count()]);
            }
        } else if ($user->hasRole('Requester')) {
            $taskCount = Task::where('admin_id', $user->id)->count();
            $usersCount = 0; // Users related to the manager via tasks

            $taskIds = Task::where('admin_id', $user->id)->get()->pluck('id')->toArray();
            // Count clients related to the tasks associated with the user
            $clientCount = Client::whereHas('clientTasks', function ($query) use ($user, $taskIds) {
                $query->whereIn('task_id', $taskIds);
            })->count();

            foreach ($taskType as $type) {
                array_push($taskTypeResponse, [$type->task_type => Task::where('task_type_id', $type->id)->whereIn('admin_id', [$user->id])->count()]);
            }

            foreach ($statuses as $status) {
                array_push($statusesTypeResponse, [$status->title => Task::where('status_id', $status->id)->whereIn('admin_id', [$user->id])->count()]);
            }

            foreach ($priorities as $priority) {
                array_push($priorityResponse, [$priority->title => Task::where('priority_id', $priority->id)->whereIn('admin_id', [$user->id])->count()]);
            }
        } else {
            $taskCount = Task::count();
            $usersCount = User::whereNotNull('client_id')->count();
            $clientCount = Client::count();


            foreach ($taskType as $type) {
                array_push($taskTypeResponse, [$type->task_type => Task::where('task_type_id', $type->id)->count()]);
            }

            foreach ($statuses as $status) {
                array_push($statusesTypeResponse, [$status->title => Task::where('status_id', $status->id)->count()]);
            }

            foreach ($priorities as $priority) {
                array_push($priorityResponse, [$priority->title => Task::where('priority_id', $priority->id)->count()]);
            }
        }
        // dd($priorityResponse);
        return view('front-end.dashboard', compact('taskTypeResponse', 'title', 'taskCount', 'taskTypeCount', 'usersCount', 'clientCount', 'statusesTypeResponse', 'priorityResponse'));
    }
    public function LoginView()
    {
        $title = 'Login';
        return view('front-end.login', compact('title'));
    }
    public function ForgotPasswordView()
    {
        $title = 'Forgot Password';
        return view('front-end.forgot_password', compact('title'));
    }
    public function TaskView()
    {
        $title = 'Task View';
        $user = User::whereNull('client_id')->role('Tasker')->get();
        $status = Status::all();
        $priority = Priority::all();
        $task_types = TaskType::all();
        $clients =  Client::all();

        $activeUser = Auth::user();
        return view('front-end.tasks', compact('title', 'user', 'status', 'priority', 'task_types', 'clients', 'activeUser'));
    }

    public function UsersView()
    {
        $title = 'Users';
        return view('front-end.users', compact('title'));
    }
    public function AddUsersView()
    {
        $title = 'Add User';
        $UserRoles = Role::all();
        return view('front-end.add_user', compact('title', 'UserRoles'));
    }
    public function statusesView()
    {
        $title = 'Statuses';
        return view('front-end.statuses', compact('title'));
    }
    public function priorityView()
    {
        $title = 'Priority';
        return view('front-end.priorities', compact('title'));
    }
    public function UserRoleView()
    {
        $title = 'User Role';
        return view('front-end.user_role', compact('title'));
    }
    public function ClientsView()
    {
        $title = 'Client view';
        $clients = Client::all();
        return view('front-end.clients', compact('title', 'clients'));
    }
    public function AddClients()
    {
        $title = 'Add Clients';
        $UserRoles = Role::all();
        return view('front-end.add_clients', compact('title', 'UserRoles'));
    }
    public function NotificationsView()
    {
        $title = 'Notifications';
        return view('front-end.notification', compact('title'));
    }
    public function TagsView()
    {
        $title = 'Tags View';
        return view('front-end.tags', compact('title'));
    }
    public function TaskBreifTemplate()
    {
        $title = 'Task Brief Template';
        $task_types = TaskType::all();
        return view('front-end.task_brief_template', compact('title', 'task_types'));
    }
    public function TaskBreifQuestion()
    {
        $title = 'Task Brief Information';
        $templates = TaskBriefTemplates::all();
        return view('front-end.task_breif_question', compact('title', 'templates'));
    }
    public function TaskInformationView(string $id)
    {
        // Get current time

        $title = 'Task Information View';
        $tasksInfo = Task::where('id', $id)->with(['taskUsers', 'taskClients', 'status', 'priority'])->first();
        $requester = User::where('id', $tasksInfo->admin_id)->first();
        // dd($tasksInfo->questionAnswers);
        $briefQuestions = [];
        $briefChecklists = [];

        foreach ($tasksInfo->taskType->briefTemplate as $briefTemplate) {
            $taskbriefTemp = TaskBriefTemplates::find($briefTemplate->id);
            $briefQuestions[] = $taskbriefTemp->briefQuestions;
            $briefChecklists[] = $taskbriefTemp->briefchecks;
        }

        // dd($briefChecklists);
        $statuses = Status::all();
        $current_User = User::where('id', Auth::user()->id)->first();
        // Assuming 'end_date' is a DateTime or Carbon instance
        $deadline = \Carbon\Carbon::parse($tasksInfo->end_date);
        $current_time = now();
        // Check if the deadline has passed
        $is_expire = ($current_time > $deadline) ? 'EXPIRED' : '';
        return view('front-end.task_information', compact('title', 'tasksInfo', 'current_User', 'briefQuestions', 'briefChecklists', 'statuses', 'requester','is_expire'));
    }

    public function ViewCheckBrief()
    {
        $title = 'Check Brief Item';
        $templates = TaskBriefTemplates::all();
        return view('front-end.task_check_brief', compact('title', 'templates'));
    }
    public function TaskTypeView()
    {
        $title = 'Task Type';
        return view('front-end.tasktype', compact('title'));
    }
}
