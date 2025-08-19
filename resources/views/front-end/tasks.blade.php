@include('layout.header')
@php
    $flag =
        Request::segment(1) == 'home' ||
        Request::segment(1) == 'users' ||
        Request::segment(1) == 'clients' ||
        (isset($viewAssigned) && $viewAssigned == 1) ||
        (Request::segment(1) == 'projects' && Request::segment(2) == 'information' && Request::segment(3) != null)
            ? 0
            : 1;

    $visibleColumns = getUserPreferences('tasks');
@endphp
@if ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0))
    <div class="<?= $flag == 1 ? 'card ' : '' ?>mt-2">
@endif
@if ($flag == 1 && ((isset($tasks) && $tasks > 0) || (isset($emptyState) && $emptyState == 0)))
    <div class="card-body">
@endif

<div class="layout-wrapper layout-content-navbar">

    <div class="layout-container">
        @include('layout.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')
            <!-- / Navbar -->
            <!-- Content wrapper -->
            <!-- Layout wrapper -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between mb-2 mt-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('dashboard.view') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">{{ $title }}</li>
                                </ol>
                            </nav>
                        </div>
                        @php
                            $permissions = session('permissions');
                        @endphp
                        <div>
                            @if (in_array('create_tasks', $permissions))
                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                data-bs-target="#create_task_modal"><button type="button"
                                    class="btn btn-sm btn-primary action_create_tasks" data-bs-toggle="tooltip"
                                    data-bs-placement="right" data-bs-original-title=" Create task"><i
                                        class="bx bx-plus"></i></button></a>
                            @endif
                        </div>
                    </div>
                    <!-- tasks -->
                    <div class="card mt-2">
                        <div class="card-body">
                            <div class="col-md-4 mb-3">
                                <select class="form-control" id="task_status_filter" name="status_ids[]" multiple="multiple"
                                    data-placeholder="<?= get_label('select_statuses', 'Select Statuses') ?>">
                                    @foreach ($status as $statuses)
                                        <option value="{{ $statuses->id }}" >{{ $statuses->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <select class="form-control" id="task_priority_filter" name="priority_ids[]" multiple="multiple"
                                    data-placeholder="<?= get_label('select_priorities', 'Select Priorities') ?>">
                                    @foreach ($priority as $priorit)
                                        <option value="{{ $priorit->id }}">{{ $priorit->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="task_start_date_from" id="task_start_date_from">
                            <input type="hidden" name="task_start_date_to" id="task_start_date_to">

                            <input type="hidden" name="task_end_date_from" id="task_end_date_from">
                            <input type="hidden" name="task_end_date_to" id="task_end_date_to">

                            <div class="table-responsive text-nowrap">
                                <input type="hidden" id="data_type" value="tasks">
                                <input type="hidden" id="data_table" value="task_table">
                                {{-- <input type="hidden" id="save_column_visibility"> --}}
                                <table id="task_table" data-toggle="table" data-loading-template="loadingTemplate"
                                    data-url="{{ route('tasks.list') }}" data-icons-prefix="bx" data-icons="icons"
                                    data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                    data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                                    data-search="true" data-side-pagination="server" data-show-columns="true"
                                    data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                    data-mobile-responsive="true" data-query-params="queryParamsTasks">
                                    <thead>
                                        <tr>
                                            <th data-checkbox="true"></th>
                                            <th data-field="id"
                                                data-visible="true"
                                                data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                            <th data-field="title"
                                                data-visible="true"
                                                data-sortable="true">{{ get_label('task', 'Task') }}</th>

                                            <th data-field="users"
                                                data-visible="true">
                                                {{ get_label('users', 'Users') }}</th>
                                            <th data-field="clients"
                                                data-visible="true">
                                                {{ get_label('clients', 'Clients') }}</th>
                                            <th data-field="status_id" class="status-column"
                                                data-visible="true"
                                                data-sortable="true">{{ get_label('status', 'Status') }}</th>
                                            <th data-field="priority_id" class="priority-column"
                                                data-visible="true"
                                                data-sortable="true">{{ get_label('priority', 'Priority') }}</th>
                                            <th data-field="start_date"
                                                data-visible="true"
                                                data-sortable="true">{{ get_label('starts_at', 'Starts at') }}</th>
                                            <th data-field="close_deadline"
                                                data-visible="true"
                                                data-sortable="true">
                                                {{ get_label('close_deadline', 'Close By Deadline') }}</th>
                                            {{-- <th data-field="created_at"
                                                data-visible="true"
                                                data-sortable="true"><?= //get_label('created_at', 'Created at') ?></th>
                                            <th data-field="updated_at"
                                                data-visible="true"
                                                data-sortable="true"><?= //get_label('updated_at', 'Updated at') ?></th> --}}
                                            <th data-field="actions"
                                                data-visible="true">
                                                {{ get_label('actions', 'Actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <script>
                            var label_update = 'Update';
                            var label_delete = 'Delete';
                            var label_duplicate = 'Duplicate';
                            var label_not_assigned = 'Not assigned';
                            var add_favorite = 'Click to mark as favorite';
                            var remove_favorite = 'Click to remove from favorite';
                            var id = '';
                        </script>
                        <script src="{{ asset('front-end/assets/js/pages/tasks.js') }}"></script>
                    </div>
                    {{-- Task show this when task will zero --}}
                    {{-- <div class="card empty-state text-center">
                        <div class="card-body">
                            <div class="misc-wrapper">
                                <h2 class="mx-2 mb-2">
                                    Tasks Not Found </h2>
                                <p class="mx-2 mb-4">Oops! 😖
                                    Data does not exists.</p>
                                <a class="btn btn-primary m-1" href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#create_task_modal">
                                    <!-- Your link text here -->
                                    Create now</a>
                                <div class="mt-3">
                                    <img src="#" alt="page-misc-error-light"
                                        width="500" class="img-fluid"
                                        data-app-dark-img="illustrations/page-misc-error-dark.png"
                                        data-app-light-img="illustrations/page-misc-error-light.png" />
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    {{-- Task show this when task will zero --}}
                    {{-- </div> --}}
                    <div class="modal fade" id="create_task_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <form action="{{ route('tasks.store') }}" class="form-submit-event modal-content"
                                method="POST">
                                @csrf
                                <input type="hidden" name="table" value="task_table">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Create task </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="title" class="form-label">Task Name <span
                                                    class="asterisk">*</span></label>
                                            <input class="form-control" id="title" type="text" name="title"
                                                placeholder="Please enter task name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="status">Status <span
                                                    class="asterisk">*</span></label>
                                            <div class="input-group">
                                                <select class="form-select " name="status_id">
                                                    @foreach ($status as $statuses)
                                                        <option value="{{ $statuses->id }}">
                                                            {{ $statuses->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Priority</label>
                                            <div class="input-group">
                                                <select class="form-control" name="priority_id"
                                                    data-placeholder="Type to search">
                                                    @foreach ($priority as $priorityItem)
                                                        <option value="{{ $priorityItem->id }}">
                                                            {{ $priorityItem->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="start_date">Start Date <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" id="task_start_date" name="start_date"
                                                class="form-control" value="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="task_end_date">End Date <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" id="task_end_date" name="end_date"
                                                class="form-control" value="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="due_date">close by deadline <span
                                                    class="asterisk">*</span></label>
                                            <select name="close_deadline" id=""
                                                class="form-select text-capitalize js-example-basic-multiple">
                                                <option value="0">No</option>
                                                <option value="1">yes</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label" for="task_type_id">Task Type <span
                                                    class="asterisk">*</span></label>
                                            <div class="input-group">
                                                <select class="form-control selectTaskProject" name="task_type_id"
                                                    data-placeholder="Type to search">
                                                    <option value="">Select Task Type</option>
                                                    @foreach ($task_types as $task_type)
                                                        <option value="{{ $task_type->id }}">
                                                            {{ $task_type->task_type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="selectTaskUsers">
                                        <div class="mb-3">
                                            <label class="form-label" for="user_id">Select Tasker <span
                                                    id="users_associated_with_project"></span></label>
                                            <div class="input-group">
                                                <select class="form-control js-example-basic-multiple"
                                                    name="users_id[]" multiple="multiple"
                                                    data-placeholder="Type to search">
                                                    @foreach ($user as $users)
                                                        <option value="{{ $users->id }}">{{ $users->first_name }}
                                                            {{ $users->last_name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="selectTaskUsers">
                                        <div class="mb-3">
                                            <label class="form-label" for="user_id">Select Clients <span
                                                    id="users_associated_with_project"></span></label>
                                            <div class="input-group">
                                                <select class="form-control js-example-basic-multiple"
                                                    name="client_id[]" multiple="multiple"
                                                    data-placeholder="Type to search">
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client->id }}">{{ $client->first_name }}
                                                            {{ $client->last_name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>
                                    </div>




                                    <div class="row">

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control description" rows="5" name="description" placeholder="Please enter description"></textarea>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label">Note</label>
                                            <textarea class="form-control" name="note" rows="3" placeholder="Optional Note"></textarea>
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
                    {{-- Update --}}
                    <div class="modal fade" id="edit_task_modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <form action="{{ route('tasks.update') }}" class="form-submit-event modal-content"
                                method="put">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel1">Update Task </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <input type="hidden" name="id" id="task_id">
                                            <label for="title" class="form-label">Task Name <span
                                                    class="asterisk">*</span></label>
                                            <input class="form-control" id="update_title" type="text"
                                                name="title" placeholder="Please enter task name">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="status">Status <span
                                                    class="asterisk">*</span></label>
                                            <div class="input-group">
                                                <select class="form-select " id="update_status" name="status_id">
                                                    @foreach ($status as $statuses)
                                                        <option value="{{ $statuses->id }}">
                                                            {{ $statuses->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Priority</label>
                                            <div class="input-group">
                                                <select class="form-control" id="priority_update" name="priority_id"
                                                    data-placeholder="Type to search">
                                                    <option value="">Select Priority</option>
                                                    @foreach ($priority as $priorityItem)
                                                        <option value="{{ $priorityItem->id }}">
                                                            {{ $priorityItem->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="start_date">Start Date<span
                                                    class="asterisk">*</span></label>
                                            <input type="text" id="update_task_start_date" name="start_date"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="task_end_date">End Date <span
                                                    class="asterisk">*</span></label>
                                            <input type="text" id="update_task_end_date" name="end_date"
                                                class="form-control" value="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label" for="close_dead_line">close by deadline <span
                                                    class="asterisk">*</span></label>
                                            <select name="close_deadline" id="close_deadline_update"
                                                class="form-select text-capitalize js-example-basic-multiple">
                                                <option value="0">No</option>
                                                <option value="1">yes</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label" for="task_type_id">Task Type <span
                                                    class="asterisk">*</span></label>
                                            <div class="input-group">
                                                <select class="form-control selectTaskProject"
                                                    id="update_task_type_id" name="task_type_id"
                                                    data-placeholder="Type to search">
                                                    <option value="">Select Task Type</option>
                                                    @foreach ($task_types as $task_type)
                                                        <option value="{{ $task_type->id }}">
                                                            {{ $task_type->task_type }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="selectTaskUsers">
                                        <div class="mb-3">
                                            <label class="form-label" for="user_id">Select Tasker <span
                                                    id="users_associated_with_project"></span></label>
                                            <div class="input-group">
                                                <select class="form-control js-example-basic-multiple"
                                                    id="update_users_id" name="users_id[]" multiple="multiple"
                                                    data-placeholder="Type to search">
                                                    @foreach ($user as $users)
                                                        <option value="{{ $users->id }}">{{ $users->first_name }}
                                                            {{ $users->last_name }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="selectTaskUsers">
                                        <div class="mb-3">
                                            <label class="form-label" for="user_id">Select Clients <span
                                                    id="users_associated_with_project"></span></label>
                                            <div class="input-group">
                                                <select class="form-control js-example-basic-multiple"
                                                    name="client_id[]" id="update_client_id" multiple="multiple"
                                                    data-placeholder="Type to search">
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client->id }}">{{ $client->first_name }}
                                                            {{ $client->last_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control description" rows="5" id="update_discription" name="description"
                                                placeholder="Please enter description"></textarea>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="mb-3">
                                            <label class="form-label">Note</label>
                                            <textarea class="form-control" name="note" id="update_note" rows="3" placeholder="Optional Note"></textarea>
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
                    {{-- Delete --}}
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close">
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
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close">
                                        '</button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete selected record(s)?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Close </button>
                                    <button type="submit" class="btn btn-danger"
                                        id="confirmDeleteSelections">Yes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('layout.labels')
                    @include('layout.footer_bottom')
                </div>
                <!-- Content wrapper -->
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
