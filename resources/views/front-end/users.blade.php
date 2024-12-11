@include('layout.header')
    @php
    $visibleColumns = getUserPreferences('users');
    @endphp
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between mb-2 mt-4">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('dashboard.view') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{$title}} </li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            @php
                                $permissions = session('permissions');
                            @endphp
                            @if (in_array('create_users', $permissions))
                            <a href="{{route('add.user.view')}}"><button type="button"
                                    class="btn btn-sm btn-primary action_create_users" data-bs-toggle="tooltip"
                                    data-bs-placement="left" data-bs-original-title="Create user"><i
                                    class='bx bx-plus'></i></button></a>
                            @endif

                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <select class="form-select" id="user_status_filter"
                                        aria-label="Default select example">
                                        <option value="">Select status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Deactive</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <select class="form-control js-example-basic-multiple" id="user_roles_filter"
                                        multiple="multiple" data-placeholder="Select Roles">
                                        <option value="1">Admin</option>
                                        <option value="9">Member</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <input type="hidden" id="data_type" value="users">
                                {{-- <input type="hidden" id="save_column_visibility"> --}}
                                <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ route('users.list') }}" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th data-checkbox="true"></th>
                                            <th data-field="id" data-visible="{{ (in_array('id', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                            <th data-field="profile" data-visible="{{ (in_array('profile', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('users', 'Users') ?></th>
                                            <th data-field="role" data-visible="{{ (in_array('role', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('role', 'Role') ?></th>
                                            <th data-field="phone" data-visible="{{ (in_array('phone', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('phone_number', 'Phone number') ?></th>
                                            {{-- <th data-field="assigned" data-visible="{{ (in_array('assigned', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}">{{ get_label('assigned', 'Assigned') }}</th> --}}
                                            <th data-field="created_at" data-visible="{{ (in_array('created_at', $visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                                            <th data-field="updated_at" data-visible="{{ (in_array('updated_at', $visibleColumns)) ? 'true' : 'false' }}" data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                                            <th data-field="actions" data-visible="{{ (in_array('actions', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('actions', 'Actions') ?></th>
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
                    var label_projects = 'Projects';
                    var label_tasks = 'Tasks';
                </script>
                <script src="{{ asset('front-end/assets/js/pages/users.js') }}"></script>

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
