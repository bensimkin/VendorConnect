@include('layout.header')

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')
        <!-- Menu -->
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')
            <!-- Navbar -->
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1">
                                    <li class="breadcrumb-item">
                                        <a href="#">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{ $title }}</li>

                                </ol>
                            </nav>
                        </div>
                        <div>
                            @php
                                $permissions = session('permissions');
                            @endphp
                            @if (in_array('create_user_role', $permissions))
                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                data-bs-target="#create_status_modal"><button type="button"
                                    class="btn btn-sm btn-primary user_role_model_open" data-bs-toggle="tooltip"
                                    data-bs-placement="right" data-bs-original-title=" Create User role"><i
                                        class="bx bx-plus"></i></button></a>
                            @endif
                        </div>
                    </div>
                    <!-- meetings -->

                    <div class="card ">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                {{-- <input type="hidden" id="data_type" value="status"> --}}
                                <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                    data-url="{{ route('user.role.list') }}" data-icons-prefix="bx" data-icons="icons"
                                    data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                    data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            {{-- <th data-checkbox="true"></th> --}}
                                            <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                                            <th data-sortable="true" data-field="role"><?= get_label('role', 'Role') ?>
                                            </th>
                                            <th data-sortable="true" data-field="created_at" data-visible="false">
                                                <?= get_label('created_at', 'Created at') ?></th>
                                            <th data-sortable="true" data-field="updated_at" data-visible="false">
                                                <?= get_label('updated_at', 'Updated at') ?></th>
                                            <th data-field="actions"><?= get_label('actions', 'Actions') ?>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <script>
                    var label_update = 'Update';
                    var label_delete = 'Delete';
                </script>

                <script src="{{ asset('front-end/assets/js/pages/user_role.js') }}"></script>

                <div class="modal fade" id="create_user_role_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event" action="{{ route('add.role') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create User Role</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Add User Role <span
                                                class="asterisk">*</span></label>
                                        <input type="text" id="nameBasic" class="form-control" name="user_role"
                                            placeholder="Please enter user role" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">permission <span
                                                class="asterisk">*</span></label>
                                        <div class="form-group">
                                            <label for="tasks">Tasks</label>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task" name="role_ids[]"
                                                        value="create_tasks">
                                                    <label for="create_task">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task" name="role_ids[]"
                                                        value="edit_tasks">
                                                    <label for="edit_task">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task" name="role_ids[]"
                                                        value="delete_tasks">
                                                    <label for="delete_task">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task" name="role_ids[]"
                                                        value="manage_tasks">
                                                    <label for="manage_task">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="task-type">Task Type</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task_type" name="role_ids[]"
                                                        value="create_task_types">
                                                    <label for="create_task_type">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task_type" name="role_ids[]"
                                                        value="edit_task_types">
                                                    <label for="edit_task_type">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task_type" name="role_ids[]"
                                                        value="delete_task_types">
                                                    <label for="delete_task_type">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task_type" name="role_ids[]"
                                                        value="manage_task_types">
                                                    <label for="manage_task_type">Manage</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-3">
                                            <label for="task-brief-templates">Task Brief Templates</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task_brief_templates"
                                                        name="role_ids[]" value="create_task_brief_templates">
                                                    <label for="create_task_brief_templates">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task_brief_templates"
                                                        name="role_ids[]" value="edit_task_brief_templates">
                                                    <label for="edit_task_brief_templates">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task_brief_templates"
                                                        name="role_ids[]" value="delete_task_brief_templates">
                                                    <label for="delete_task_brief_templates">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task_brief_templates"
                                                        name="role_ids[]" value="manage_task_brief_templates">
                                                    <label for="manage_task_brief_templates">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="task-brief-question">Task Brief Question</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task_brief_question"
                                                        name="role_ids[]" value="create_task_brief_question">
                                                    <label for="create_task_brief_question">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task_brief_question"
                                                        name="role_ids[]" value="edit_task_brief_question">
                                                    <label for="edit_task_brief_question">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task_brief_question"
                                                        name="role_ids[]" value="delete_task_brief_question">
                                                    <label for="delete_task_brief_question">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task_brief_question"
                                                        name="role_ids[]" value="manage_task_brief_question">
                                                    <label for="manage_task_brief_question">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="statuses">Statuses</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_status" name="role_ids[]"
                                                        value="create_statuses">
                                                    <label for="create_status">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_status" name="role_ids[]"
                                                        value="edit_statuses">
                                                    <label for="edit_status">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_status" name="role_ids[]"
                                                        value="delete_statuses">
                                                    <label for="delete_status">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_status" name="role_ids[]"
                                                        value="manage_statuses">
                                                    <label for="manage_status">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="priorities">Priorities</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_priority" name="role_ids[]"
                                                        value="create_priorities">
                                                    <label for="create_priority">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_priority" name="role_ids[]"
                                                        value="edit_priorities">
                                                    <label for="edit_priority">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_priority" name="role_ids[]"
                                                        value="delete_priorities">
                                                    <label for="delete_priority">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_priority" name="role_ids[]"
                                                        value="manage_priorities">
                                                    <label for="manage_priority">Manage</label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="form-group mt-3">
                                            <label for="tags">Tags</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_tags" name="role_ids[]"
                                                        value="create_tags">
                                                    <label for="create_tags">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_tags" name="role_ids[]"
                                                        value="edit_tags">
                                                    <label for="edit_tags">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_tags" name="role_ids[]"
                                                        value="delete_tags">
                                                    <label for="delete_tags">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_tags" name="role_ids[]"
                                                        value="manage_tags">
                                                    <label for="manage_tag">Manage</label>
                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="form-group mt-3">
                                            <label for="users">Users</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_user" name="role_ids[]"
                                                        value="create_users">
                                                    <label for="create_user">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_user" name="role_ids[]"
                                                        value="edit_users">
                                                    <label for="edit_user">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_user" name="role_ids[]"
                                                        value="delete_users">
                                                    <label for="delete_user">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_user" name="role_ids[]"
                                                        value="manage_users">
                                                    <label for="manage_user">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="clients">User Role</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_user_role"
                                                        value="create_user_role" name="role_ids[]">
                                                    <label for="create_user_role">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="create_user_role" name="role_ids[]"
                                                        value="create_user_role">
                                                    <label for="create_user_role">Edit </label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_user_role" name="role_ids[]"
                                                        value="delete_user_role">
                                                    <label for="delete_user_role">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_user_role" name="role_ids[]"
                                                        value="manage_user_role">
                                                    <label for="manage_user_role">Manage</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-3">
                                            <label for="clients">Clients</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_client" name="role_ids[]"
                                                        value="create_clients">
                                                    <label for="create_client">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_client" name="role_ids[]"
                                                        value="edit_clients">
                                                    <label for="edit_client">Edit </label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_client" name="role_ids[]"
                                                        value="delete_clients">
                                                    <label for="delete_client">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_client" name="role_ids[]"
                                                        value="manage_clients">
                                                    <label for="manage_client">Manage</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close</label>
                                </button>
                                <button type="submit" class="btn btn-primary"
                                    id="submit_btn">Create</label></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="edit_role_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('user.role.update') }}" class="modal-content form-submit-event"
                            method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Update Role </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="user_role" class="form-label">Role <span
                                                class="asterisk">*</span></label>
                                        <input type="hidden" name="id" id="user_role_id">
                                        <input type="text" id="user_role" class="form-control" name="user_role"
                                            placeholder="Please enter role" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">permission <span
                                                class="asterisk">*</span></label>
                                        <div class="form-group">
                                            <label for="tasks">Tasks</label>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task" name="role_ids[]"
                                                        value="create_tasks">
                                                    <label for="create_task">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task" name="role_ids[]"
                                                        value="edit_tasks">
                                                    <label for="edit_task">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task" name="role_ids[]"
                                                        value="delete_tasks">
                                                    <label for="delete_task">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task" name="role_ids[]"
                                                        value="manage_tasks">
                                                    <label for="manage_task">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="task-type">Task Type</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task_type" name="role_ids[]"
                                                        value="create_task_types">
                                                    <label for="create_task_type">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task_type" name="role_ids[]"
                                                        value="edit_task_types">
                                                    <label for="edit_task_type">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task_type" name="role_ids[]"
                                                        value="delete_task_types">
                                                    <label for="delete_task_type">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task_type" name="role_ids[]"
                                                        value="manage_task_types">
                                                    <label for="manage_task_type">Manage</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-3">
                                            <label for="task-brief-templates">Task Brief Templates</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task_brief_templates"
                                                        name="role_ids[]" value="create_task_brief_templates">
                                                    <label for="create_task_brief_templates">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task_brief_templates"
                                                        name="role_ids[]" value="edit_task_brief_templates">
                                                    <label for="edit_task_brief_templates">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task_brief_templates"
                                                        name="role_ids[]" value="delete_task_brief_templates">
                                                    <label for="delete_task_brief_templates">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task_brief_templates"
                                                        name="role_ids[]" value="manage_task_brief_templates">
                                                    <label for="manage_task_brief_templates">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="task-brief-question">Task Brief Question</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_task_brief_question"
                                                        name="role_ids[]" value="create_task_brief_question">
                                                    <label for="create_task_brief_question">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_task_brief_question"
                                                        name="role_ids[]" value="edit_task_brief_question">
                                                    <label for="edit_task_brief_question">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_task_brief_question"
                                                        name="role_ids[]" value="delete_task_brief_question">
                                                    <label for="delete_task_brief_question">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_task_brief_question"
                                                        name="role_ids[]" value="manage_task_brief_question">
                                                    <label for="manage_task_brief_question">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="statuses">Statuses</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_status" name="role_ids[]"
                                                        value="create_statuses">
                                                    <label for="create_status">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_status" name="role_ids[]"
                                                        value="edit_statuses">
                                                    <label for="edit_status">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_status" name="role_ids[]"
                                                        value="delete_statuses">
                                                    <label for="delete_status">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_status" name="role_ids[]"
                                                        value="manage_statuses">
                                                    <label for="manage_status">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="priorities">Priorities</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_priority" name="role_ids[]"
                                                        value="create_priorities">
                                                    <label for="create_priority">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_priority" name="role_ids[]"
                                                        value="edit_priorities">
                                                    <label for="edit_priority">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_priority" name="role_ids[]"
                                                        value="delete_priorities">
                                                    <label for="delete_priority">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_priority" name="role_ids[]"
                                                        value="manage_priorities">
                                                    <label for="manage_priority">Manage</label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="form-group mt-3">
                                            <label for="tags">Tags</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_tags" name="role_ids[]"
                                                        value="create_tags">
                                                    <label for="create_tags">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_tags" name="role_ids[]"
                                                        value="edit_tags">
                                                    <label for="edit_tags">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_tags" name="role_ids[]"
                                                        value="delete_tags">
                                                    <label for="delete_tags">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_tags" name="role_ids[]"
                                                        value="manage_tags">
                                                    <label for="manage_tag">Manage</label>
                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="form-group mt-3">
                                            <label for="users">Users</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_user" name="role_ids[]"
                                                        value="create_users">
                                                    <label for="create_user">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_user" name="role_ids[]"
                                                        value="edit_users">
                                                    <label for="edit_user">Edit</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_user" name="role_ids[]"
                                                        value="delete_users">
                                                    <label for="delete_user">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_user" name="role_ids[]"
                                                        value="manage_users">
                                                    <label for="manage_user">Manage</label>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-group mt-3">
                                            <label for="clients">User Role</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_user_role"
                                                        value="create_user_role" name="role_ids[]">
                                                    <label for="create_user_role">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="create_user_role" name="role_ids[]"
                                                        value="create_user_role">
                                                    <label for="create_user_role">Edit </label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_user_role" name="role_ids[]"
                                                        value="delete_user_role">
                                                    <label for="delete_user_role">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_user_role" name="role_ids[]"
                                                        value="manage_user_role">
                                                    <label for="manage_user_role">Manage</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-3">
                                            <label for="clients">Clients</label><br>
                                            <div style="display: flex; gap: 40px; margin-top: 10px;">
                                                <div>
                                                    <input type="checkbox" id="create_client" name="role_ids[]"
                                                        value="create_clients">
                                                    <label for="create_client">Create</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="edit_client" name="role_ids[]"
                                                        value="edit_clients">
                                                    <label for="edit_client">Edit </label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="delete_client" name="role_ids[]"
                                                        value="delete_clients">
                                                    <label for="delete_client">Delete</label>
                                                </div>
                                                <div>
                                                    <input type="checkbox" id="manage_client" name="role_ids[]"
                                                        value="manage_clients">
                                                    <label for="manage_client">Manage</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close</label>
                                </button>
                                <button type="submit" class="btn btn-primary"
                                    id="submit_btn">Update</label></button>
                            </div>
                        </form>
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
            @include('layout.labels')
            @include('layout.footer_bottom')

        </div>
        <!-- Content wrapper -->
        <!-- Footer -->
    </div>
    <!-- / Footer -->
    <!-- / footer -->
</div>
<!-- / Layout page -->
</div>
<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout page -->
@include('layout.footer_links')

</script>
</body>

</html>
