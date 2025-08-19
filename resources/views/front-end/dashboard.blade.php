@include('layout.header')

@include('layout.sidebar')
<!-- Layout container -->
<div class="layout-page">
    @include('layout.navbar')
    <!-- / Navbar -->
    <!-- Content wrapper -->
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="col-lg-12 col-md-12 order-1">
                <div class="row mt-4">
                    @include('components.dashboard-card', [
                        'title' => 'Tasks',
                        'count' => $taskCount,
                        'icon' => 'bx-briefcase-alt-2',
                        'color' => 'success',
                        'link' => route('task.view')
                    ])
                    
                    @include('components.dashboard-card', [
                        'title' => 'Task Types',
                        'count' => $taskTypeCount,
                        'icon' => 'bx-task',
                        'color' => 'primary',
                        'link' => route('tasktype.view')
                    ])
                    
                    @include('components.dashboard-card', [
                        'title' => 'Total Users',
                        'count' => $usersCount,
                        'icon' => 'bxs-user-detail',
                        'color' => 'warning',
                        'link' => route('user.view')
                    ])
                    
                    @include('components.dashboard-card', [
                        'title' => 'Total Clients',
                        'count' => $clientCount,
                        'icon' => 'bxs-user-detail',
                        'color' => 'info',
                        'link' => route('clients.view')
                    ])
                </div>
                <div class="row">
                    <div class="col-md-4 col-sm-12">
                        <div class="card statisticsDiv mb-4 overflow-hidden">
                            <div class="card-header pb-1 pt-3">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2 mt-4">Task Types of Tasks</h5>
                                </div>
                                {{-- <div class="my-3">
                                            <div id="projectStatisticsChart"></div>
                                        </div> --}}
                            </div>
                            <div class="card-body" id="project-statistics">
                                <ul class="m-0 p-0 list-unstyled task_list">
                                    @foreach ($taskTypeResponse as $taskType)
                                        @foreach ($taskType as $key => $value)
                                            <li class="d-flex mb-4 pb-1 mt-4">
                                                <div class="avatar me-3 flex-shrink-0">
                                                    <span class="avatar-initial bg-label-primary rounded"><i
                                                            class="bx bx-briefcase-alt-2 text-{{ $key }}"></i></span>
                                                </div>
                                                <div
                                                    class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                                    <div class="me-2">
                                                        <a href="#">
                                                            <h6 class="mb-0">{{ $key }}</h6>
                                                        </a>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">{{ $value }}</small>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
                        <div class="card statisticsDiv mb-4 overflow-hidden">
                            <div class="card-header pb-1 pt-3">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2 mt-4">Statuses of Tasks</h5>
                                </div>
                            </div>
                            <div class="card-body" id="task-statistics">
                                <ul class="m-0 p-0">
                                    @foreach ($statusesTypeResponse as $statusesType)
                                        @foreach ($statusesType as $key => $value)
                                            <li class="d-flex mb-4 pb-1 mt-4">
                                                <div class="avatar me-3 flex-shrink-0">
                                                    <span class="avatar-initial bg-label-primary rounded"><i
                                                            class="bx bx-task text-primary"></i></span>
                                                </div>
                                                <div
                                                    class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                                    <div class="me-2">
                                                        <a href="#">
                                                            <h6 class="mb-0">{{ $key }}</h6>
                                                        </a>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">{{ $value }}</small>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
                        <div class="card statisticsDiv mb-4 overflow-hidden">
                            <div class="card-header pb-1 pt-3">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2 mt-4">Priority of Tasks</h5>
                                </div>
                            </div>
                            <div class="card-body" id="task-statistics">
                                <ul class="m-0 p-0">
                                    @foreach ($priorityResponse as $priority)
                                        @foreach ($priority as $key => $value)
                                            <li class="d-flex mb-4 pb-1 mt-4">
                                                <div class="avatar me-3 flex-shrink-0">
                                                    <span class="avatar-initial bg-label-primary rounded"><i
                                                            class="bx bx-task text-primary"></i></span>
                                                </div>
                                                <div
                                                    class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                                    <div class="me-2">
                                                        <a href="#">
                                                            <h6 class="mb-0">{{ $key }}</h6>
                                                        </a>
                                                    </div>
                                                    <div class="user-progress">
                                                        <small class="fw-semibold">{{ $value }}</small>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{--  --}}
            <!-- ------------------------------------------- -->
        </div>
        <script>
            var labels = [];
            var project_data = [];
            var task_data = [];
            var bg_colors = [];
            var total_projects = [0];
            var total_tasks = [0];
            var total_todos = [0];
            var todo_data = [0, 0];
            //labels
            var done = 'Done ';
            var pending = 'Pending ';
            var total = 'Total ';
        </script>
        <script src="https://app.inth.pk/assets/js/apexcharts.js"></script>
        <script src="https://app.inth.pk/assets/js/pages/dashboard.js"></script>
        <div class="modal fade" id="create_todo_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form class="modal-content form-submit-event" action="https://app.inth.pk/master-panel/todos/store"
                    method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Create todo </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                        autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Title <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="title"
                                    placeholder="Please enter title" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Priority <span
                                        class="asterisk">*</span></label>
                                <select class="form-select" name="priority">
                                    <option value="low">
                                        Low</option>
                                    <option value="medium">
                                        Medium</option>
                                    <option value="high">
                                        High</option>
                                </select>
                            </div>
                        </div>
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control description" name="description" placeholder="Please enter description"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="edit_todo_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="https://app.inth.pk/master-panel/todos/update" class="modal-content form-submit-event"
                    method="POST">
                    <input type="hidden" name="id" id="todo_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">
                            Update todo</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                        autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Title <span
                                        class="asterisk">*</span></label>
                                <input type="text" id="todo_title" class="form-control" name="title"
                                    placeholder="Please enter title" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Priority <span
                                        class="asterisk">*</span></label>
                                <select class="form-select" id="todo_priority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control description" id="todo_description" name="description"
                            placeholder="Please enter description"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="submit_btn">Update</span></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="default_language_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you want to set as your primary language?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirm">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="leaveWorkspaceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Warning</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want leave this workspace? </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-danger" id="confirm">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create_language_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form class="modal-content form-submit-event"
                    action="https://app.inth.pk/superadmin/settings/languages/store" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Create language </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                        autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Title <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="name"
                                    placeholder="For Example: English" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Code <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="code"
                                    placeholder="For Example: en" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="edit_language_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <form class="modal-content form-submit-event"
                    action="https://app.inth.pk/superadmin/settings/languages/update" method="POST">
                    <input type="hidden" name="id" id="language_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Update language </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                        autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Title <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="name" id="language_title"
                                    placeholder="For Example: English" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="create_contract_type_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <form class="modal-content form-submit-event"
                    action="https://app.inth.pk/master-panel/contracts/store-contract-type" method="POST">
                    <input type="hidden" name="dnr">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">
                            Create contract type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Type <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="type"
                                    placeholder="Please enter contract type" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="edit_contract_type_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <form class="modal-content form-submit-event"
                    action="https://app.inth.pk/master-panel/contracts/update-contract-type" method="POST">
                    <input type="hidden" name="dnr">
                    <input type="hidden" id="update_contract_type_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">
                            Update contract type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">Type <span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" name="type" id="contract_type"
                                    placeholder="Please enter contract type" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Warning</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete your account?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <form id="formAccountDeactivation" action="https://app.inth.pk/master-panel/account/destroy/3"
                            method="POST">
                            <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                                autocomplete="off"> <input type="hidden" name="_method" value="DELETE"> <button
                                type="submit" class="btn btn-danger">Yes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            '</button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-danger" id="confirmDelete">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="confirmDeleteSelectedModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            '</button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete selected record(s)?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteSelections">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Warning</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to duplicate?</p>
                        <div id="titleDiv" class="d-none"><label class="form-label">Update
                                Title</label><input type="text" class="form-control" id="updateTitle"
                                placeholder="Enter Title For Item Being Duplicated"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirmDuplicate">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="timerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Time tracker </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-body">
                            <div class="stopwatch">
                                <div class="stopwatch_time">
                                    <input type="text" name="hour" id="hour" value="00"
                                        class="form-control stopwatch_time_input" readonly>
                                    <div class="stopwatch_time_lable">Hours</div>
                                </div>
                                <div class="stopwatch_time">
                                    <input type="text" name="minute" id="minute" value="00"
                                        class="form-control stopwatch_time_input" readonly>
                                    <div class="stopwatch_time_lable">Minutes</div>
                                </div>
                                <div class="stopwatch_time">
                                    <input type="text" name="second" id="second" value="00"
                                        class="form-control stopwatch_time_input" readonly>
                                    <div class="stopwatch_time_lable">Second</div>
                                </div>
                            </div>
                            <div class="selectgroup selectgroup-pills d-flex justify-content-around mt-3">
                                <label class="selectgroup-item">
                                    <span class="selectgroup-button selectgroup-button-icon" data-bs-toggle="tooltip"
                                        data-bs-placement="left" data-bs-original-title="Start" id="start"
                                        onclick="startTimer()"><i class="bx bx-play"></i></span>
                                </label>
                                <label class="selectgroup-item">
                                    <span class="selectgroup-button selectgroup-button-icon" data-bs-toggle="tooltip"
                                        data-bs-placement="left" data-bs-original-title="Stop" id="end"
                                        onclick="stopTimer()"><i class="bx bx-stop"></i></span>
                                </label>
                                <label class="selectgroup-item">
                                    <span class="selectgroup-button selectgroup-button-icon" data-bs-toggle="tooltip"
                                        data-bs-placement="left" data-bs-original-title="Pause" id="pause"
                                        onclick="pauseTimer()"><i class="bx bx-pause"></i></span>
                                </label>
                            </div>
                            <div class="form-group mb-0 mt-3">
                                <label class="label">Message:</label>
                                <textarea class="form-control" id="time_tracker_message" placeholder="Please Enter Your Message" name="message"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <a href="https://app.inth.pk/master-panel/time-tracker" class="btn btn-primary"><i
                                    class="bx bxs-time"></i> View timesheet</a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="stopTimerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            '</button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to stop the timer?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-danger" id="confirmStop">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="mark_all_notifications_as_read_modal" tabindex="-1" style="display: none;"
            aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to mark all notifications as read? </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirmMarkAllAsRead">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="update_notification_status_modal" tabindex="-1" style="display: none;"
            aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to update notification status? </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirmNotificationStatus">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="restore_default_modal" tabindex="-1" style="display: none;"
            aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to restore default template? </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirmRestoreDefault">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="sms_instuction_modal" tabindex="-1" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Sms Gateway Configuration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-body">
                            <ul>
                                <li class="my-4">Read and follow instructions carefully while configuration
                                    sms gateway
                                    setting </li>

                                <li class="my-4">Firstly open your sms gateway account . You can find api
                                    keys in your
                                    account -> API keys & credentials -> create api key </li>
                                <li class="my-4">After create key you can see here Account sid and auth
                                    token </li>
                                <div class="simplelightbox-gallery">
                                    <a href="https://app.inth.pk/storage/images/base_url_and_params.png"
                                        target="_blank">
                                        <img src="https://app.inth.pk/storage/images/base_url_and_params.png"
                                            class="w-100">
                                    </a>
                                </div>

                                <li class="my-4">For Base url Messaging -> Send an SMS</li>
                                <div class="simplelightbox-gallery">
                                    <a href="https://app.inth.pk/storage/images/api_key_and_token.png"
                                        target="_blank">
                                        <img src="https://app.inth.pk/storage/images/api_key_and_token.png"
                                            class="w-100">
                                    </a>
                                </div>

                                <li class="my-4">check this for admin panel settings</li>
                                <div class="simplelightbox-gallery">
                                    <a href="https://app.inth.pk/storage/images/sms_gateway_1.png" target="_blank">
                                        <img src="https://app.inth.pk/storage/images/sms_gateway_1.png"
                                            class="w-100">
                                    </a>
                                </div>
                                <div class="simplelightbox-gallery">
                                    <a href="https://app.inth.pk/storage/images/sms_gateway_2.png" target="_blank">
                                        <img src="https://app.inth.pk/storage/images/sms_gateway_2.png"
                                            class="w-100">
                                    </a>
                                </div>
                                <li class="my-4"><b>Make sure you entered valid data as per instructions
                                        before proceed</b>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="edit_project_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form action="https://app.inth.pk/master-panel/projects/update"
                    class="form-submit-event modal-content" method="POST">
                    <input type="hidden" name="_method" value="PUT"> <input type="hidden" name="id"
                        id="project_id">
                    <input type="hidden" name="dnr">
                    <input type="hidden" name="table" value="projects_table">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">
                            Update project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                        autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Title <span
                                        class="asterisk">*</span></label>
                                <input class="form-control" type="text" name="title" id="project_title"
                                    placeholder="Please enter title" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status">Status <span
                                        class="asterisk">*</span></label>
                                <div class="input-group">

                                    <select class="form-control statusDropdown" name="status_id"
                                        id="project_status_id">

                                    </select>
                                </div>
                                <div class="mt-2">
                                    <a href="javascript:void(0);" class="openCreateStatusModal"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title=" Create status"><i
                                                class="bx bx-plus"></i></button></a>
                                    <a href="https://app.inth.pk/master-panel/status/manage" target="_blank"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title="Manage statuses"><i
                                                class="bx bx-list-ul"></i></button></a>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <div class="input-group">

                                    <select class="form-select" name="priority_id" id="project_priority_id">

                                    </select>
                                </div>
                                <div class="mt-2">
                                    <a href="javascript:void(0);" class="openCreatePriorityModal"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title=" Create Priority"><i
                                                class="bx bx-plus"></i></button></a>
                                    <a href="https://app.inth.pk/master-panel/priority/manage" target="_blank"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title="Manage Priorities"><i
                                                class="bx bx-list-ul"></i></button></a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="budget" class="form-label">Budget</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">$</span>
                                    <input class="form-control" type="text" id="project_budget" name="budget"
                                        placeholder="Please enter budget" value="">
                                </div>
                                <span class="text-danger error-message"></span>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="start_date">Starts at <span
                                        class="asterisk">*</span></label>
                                <input type="text" id="update_start_date" name="start_date" class="form-control"
                                    value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="due_date">Ends at <span
                                        class="asterisk">*</span></label>
                                <input type="text" id="update_end_date" name="end_date" class="form-control"
                                    value="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="">
                                    Task Accessibility <i class='bx bx-info-circle text-primary'
                                        data-bs-toggle="tooltip" data-bs-offset="0,4" data-bs-placement="top"
                                        data-bs-html="true" title=""
                                        data-bs-original-title="<b>Assigned Users:</b> You Will Need to Manually Select Task Users When Creating Tasks Under This Project.<br><b>Project Users:</b> When Creating Tasks Under This Project, the Task Users Selection Will Be Automatically Filled With Project Users."
                                        data-bs-toggle="tooltip" data-bs-placement="top"></i>
                                </label>
                                <div class="input-group">
                                    <select class="form-select" name="task_accessibility" id="task_accessibility">
                                        <option value="assigned_users">
                                            Assigned Users</option>
                                        <option value="project_users">Project Users </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label" for="user_id">Select users</label>
                                <div class="input-group">
                                    <select class="form-control js-example-basic-multiple" name="user_id[]"
                                        multiple="multiple" data-placeholder="Type to search">

                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label" for="client_id">Select clients</label>
                                <div class="input-group">
                                    <select class="form-control js-example-basic-multiple" name="client_id[]"
                                        multiple="multiple" data-placeholder="Type to search">

                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="">Select tags</label>
                                <div class="input-group">
                                    <select class="form-control tagsDropdown" name="tag_ids[]" multiple="multiple"
                                        data-placeholder="Type to search">

                                    </select>
                                </div>

                                <div class="mt-2">
                                    <a href="javascript:void(0);" class="openCreateTagModal"><button type="button"
                                            class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title=" Create tag"><i
                                                class="bx bx-plus"></i></button></a>
                                    <a href="https://app.inth.pk/master-panel/tags/manage"><button type="button"
                                            class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title="Manage tags"><i
                                                class="bx bx-list-ul"></i></button></a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control description" rows="5" name="description" id="project_description"
                                    placeholder="Please enter description"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <textarea class="form-control" name="note" id="projectNote" rows="3" placeholder="Optional Note"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>


        <div class="modal fade" id="set_default_view_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are You Want to Set as Default View?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirm">Yes</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="edit_task_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form action="https://app.inth.pk/master-panel/tasks/update" class="form-submit-event modal-content"
                    method="POST">
                    <input type="hidden" name="_method" value="PUT"> <input type="hidden" name="id"
                        id="id">
                    <input type="hidden" name="dnr">
                    <input type="hidden" name="table" value="task_table">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Update task </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" name="_token" value="666JQNyxLLoGtnybmDx7pPnh3iRzQLGSexD7GRaA"
                        autocomplete="off">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title" class="form-label">Title <span
                                        class="asterisk">*</span></label>
                                <input class="form-control" type="text" id="title" name="title"
                                    placeholder="Please enter title" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status">Status <span
                                        class="asterisk">*</span></label>
                                <div class="input-group">
                                    <select class="form-select statusDropdown" name="status_id"
                                        id="task_status_id">
                                    </select>

                                </div>
                                <div class="mt-2">
                                    <a href="javascript:void(0);" class="openCreateStatusModal"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title=" Create status"><i
                                                class="bx bx-plus"></i></button></a>
                                    <a href="https://app.inth.pk/master-panel/status/manage" target="_blank"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title="Manage statuses"><i
                                                class="bx bx-list-ul"></i></button></a>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <div class="input-group">

                                    <select class="form-select" name="priority_id" id="priority_id">

                                    </select>
                                </div>
                                <div class="mt-2">
                                    <a href="javascript:void(0);" class="openCreatePriorityModal"><button
                                            type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                            data-bs-placement="right" data-bs-original-title=" Create Priority"><i
                                                class="bx bx-plus"></i></button></a>
                                    <a href="https://app.inth.pk/master-panel/priority/manage"
                                        target="_blank"><button type="button" class="btn btn-sm btn-primary"
                                            data-bs-toggle="tooltip" data-bs-placement="right"
                                            data-bs-original-title="Manage Priorities"><i
                                                class="bx bx-list-ul"></i></button></a>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="start_date">Starts at <span
                                        class="asterisk">*</span></label>
                                <input type="text" id="update_start_date" name="start_date"
                                    class="form-control" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="due_date">Ends at <span
                                        class="asterisk">*</span></label>
                                <input type="text" id="update_end_date" name="due_date" class="form-control"
                                    value="">
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="project_title" class="form-label">Project <span
                                        class="asterisk">*</span></label>
                                <input class="form-control" type="text" id="update_project_title" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label" for="user_id">Select users <span
                                        id="task_update_users_associated_with_project"></span></label>
                                <div class="input-group">
                                    <select class="form-control js-example-basic-multiple" name="user_id[]"
                                        multiple="multiple" data-placeholder="Type to search">
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control description" id="task_description" rows="5" name="description"
                                    placeholder="Please enter description"></textarea>
                            </div>

                        </div>
                        <div class="row">
                            <div class="mb-3">
                                <label class="form-label">Note</label>
                                <textarea class="form-control" name="note" rows="3" id="taskNote" placeholder="Optional Note"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1"><span id="typePlaceholder"></span>
                            Quick View</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h5 id="quickViewTitlePlaceholder" class="text-muted"></h5>
                        <div class="nav-align-top">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <button type="button" class="nav-link active" role="tab"
                                        data-bs-toggle="tab" data-bs-target="#navs-top-quick-view-users"
                                        aria-controls="navs-top-quick-view-users">
                                        <i class="menu-icon tf-icons bx bx-group text-primary"></i>Users
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link " role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-quick-view-clients"
                                        aria-controls="navs-top-quick-view-clients">
                                        <i class="menu-icon tf-icons bx bx-group text-warning"></i>Clients
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link " role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-quick-view-description"
                                        aria-controls="navs-top-quick-view-description">
                                        <i class="menu-icon tf-icons bx bx-notepad text-success"></i>Description
                                    </button>
                                </li>
                            </ul>
                            <input type="hidden" id="type">
                            <input type="hidden" id="typeId">
                            <div class="tab-content">
                                <div class="tab-pane fade active show" id="navs-top-quick-view-users"
                                    role="tabpanel">
                                    <div class="table-responsive text-nowrap">
                                        <!-- <input type="hidden" id="data_type" value="users">
                        <input type="hidden" id="data_table" value="usersTable"> -->
                                        <table id="usersTable" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="/master-panel/users/list" data-icons-prefix="bx"
                                            data-icons="icons" data-show-refresh="true" data-total-field="total"
                                            data-trim-on-search="false" data-data-field="rows"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                            data-side-pagination="server" data-show-columns="true"
                                            data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true"
                                            data-query-params="queryParamsUsersClients">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true"></th>
                                                    <th data-sortable="true" data-field="id">ID</th>
                                                    <th data-formatter="userFormatter" data-sortable="true"
                                                        data-field="first_name">Users</th>
                                                    <th data-field="role">Role</th>
                                                    <th data-field="phone" data-sortable="true"
                                                        data-visible="false">Phone number</th>
                                                    <th data-field="assigned">Assigned</th>
                                                    <th data-sortable="true" data-field="created_at"
                                                        data-visible="false">Created at</th>
                                                    <th data-sortable="true" data-field="updated_at"
                                                        data-visible="false">Updated at</th>

                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade " id="navs-top-quick-view-clients" role="tabpanel">
                                    <div class="table-responsive text-nowrap">
                                        <!-- <input type="hidden" id="data_type" value="clients">
                    <input type="hidden" id="data_table" value="clientsTable"> -->
                                        <table id="clientsTable" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="/master-panel/clients/list" data-icons-prefix="bx"
                                            data-icons="icons" data-show-refresh="true" data-total-field="total"
                                            data-trim-on-search="false" data-data-field="rows"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                            data-side-pagination="server" data-show-columns="true"
                                            data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true"
                                            data-query-params="queryParamsUsersClients">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true"></th>
                                                    <th data-sortable="true" data-field="id">ID</th>
                                                    <th data-formatter="clientFormatter" data-sortable="true">Client
                                                    </th>
                                                    <th data-field="company" data-sortable="true"
                                                        data-visible="false">Company</th>
                                                    <th data-field="phone" data-sortable="true"
                                                        data-visible="false">Phone number</th>
                                                    <th data-field="assigned">Assigned</th>
                                                    <th data-sortable="true" data-field="created_at"
                                                        data-visible="false">Created at</th>
                                                    <th data-sortable="true" data-field="updated_at"
                                                        data-visible="false">Updated at</th>

                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade " id="navs-top-quick-view-description" role="tabpanel">
                                    <p class="pt-3" id="quickViewDescPlaceholder"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="confirmSaveColumnVisibility" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are You Want to Save Column Visibility?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirm">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create workspace</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="https://app.inth.pk/master-panel/workspaces/store" class="form-submit-event"
                        method="POST">
                        <input type="hidden" name="dnr">
                        <div class="modal-body">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span
                                            class="asterisk">*</span></label>
                                    <input class="form-control" type="text" id="title" name="title"
                                        placeholder="Please enter title" value="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <select id="" class="form-control js-example-basic-multiple"
                                        name="user_ids[]" multiple="multiple" data-placeholder="Type to search">

                                        <option value="3" selected>
                                            ahsan ahmed
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <select id="" class="form-control js-example-basic-multiple"
                                        name="client_ids[]" multiple="multiple" data-placeholder="Type to search">
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <label class="form-check-label" for="primaryWorkspace">
                                            <input class="form-check-input" type="checkbox"
                                                name="primaryWorkspace" id="primaryWorkspace">
                                            Primary Workspace?
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-primary alert-dismissible" role="alert">
                                You will be workspace participant automatically. </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="submit_btn" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editWorkspaceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update workspace</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="https://app.inth.pk/master-panel/workspaces/update" class="form-submit-event"
                        method="POST">
                        <input type="hidden" name="_method" value="PUT"> <input type="hidden"
                            name="id" id="workspace_id">
                        <input type="hidden" name="dnr">
                        <div class="modal-body">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span
                                            class="asterisk">*</span></label>
                                    <input class="form-control" type="text" name="title"
                                        id="workspace_title" placeholder="Please enter title" value="">
                                </div>
                            </div>
                            <div class="row">

                                <div class="mb-3">
                                    <select id="" class="form-control js-example-basic-multiple"
                                        name="user_ids[]" multiple="multiple" data-placeholder="Type to search">


                                        <option value="3">
                                            ahsan ahmed
                                        </option>
                                    </select>

                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <select id="" class="form-control js-example-basic-multiple"
                                        name="client_ids[]" multiple="multiple" data-placeholder="Type to search">
                                    </select>

                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <label class="form-check-label" for="updatePrimaryWorkspace">
                                            <input class="form-check-input" type="checkbox"
                                                name="primaryWorkspace" id="updatePrimaryWorkspace">
                                            Primary Workspace?
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="submit_btn" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




        <div class="modal fade" id="confirmUpdateStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Do You Want to Update the Status?</p>
                        <textarea class="form-control" id="statusNote" placeholder="Optional Note"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" id="declineUpdateStatus"
                            data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirmUpdateStatus">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="confirmUpdatePriorityModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Do You Want to Update the Priority?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" id="declineUpdatePriority"
                            data-bs-dismiss="modal">
                            Close </button>
                        <button type="submit" class="btn btn-primary" id="confirmUpdatePriority">Yes</button>
                    </div>
                </div>
            </div>
        </div>
        @include('layout.labels')
    </div>
    <!-- Content wrapper -->
    <!-- Footer -->
    @include('layout.footer_bottom')
    <!-- / Footer -->
</div>
</div>
<div class="layout-overlay layout-menu-toggle"></div>
</div>

<!-- / Layout page -->
@include('layout.footer_links')

</script>
</body>

</html>
