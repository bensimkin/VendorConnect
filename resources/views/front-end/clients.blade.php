@include('layout.header')
<!-- Layout wrapper -->
@section('title')
    <?= get_label('clients', 'Clients') ?>
@endsection
@php
    $visibleColumns = getUserPreferences('clients');
@endphp
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')
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
                            @if (in_array('create_clients', $permissions))
                            <a href="{{route('add.clients.view')}}"><button type="button"
                                    class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                                    data-bs-original-title="Create client"><i class='bx bx-plus'></i></button></a>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <select class="form-select" id="client_status_filter" aria-label="Default select example">
                                        <option value=""><?= get_label('select_status', 'Select status') ?></option>
                                        <option value="1">{{ get_label('active', 'Active') }}</option>
                                        <option value="0">{{ get_label('deactive', 'Deactive') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <select class="form-select" id="client_internal_purpose_filter"
                                        aria-label="Default select example">
                                        <option value=""><?= get_label('all', 'All') ?></option>
                                        <option value="0">{{ get_label('normal', 'Normal') }}</option>
                                        {{-- <option value="1">{{ get_label('internal_purpose', 'Internal Purpose') }}</option> --}}
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive text-nowrap">
                                    <input type="hidden" id="data_type" value="clients">
                                    {{-- <input type="hidden" id="save_column_visibility"> --}}
                                    <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                        data-url="{{ route('clients.list') }}" data-icons-prefix="bx" data-icons="icons"
                                        data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                        data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                        data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-query-params="queryParams"
                                        data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}">

                                        <thead>

                                            <tr>
                                                <th data-checkbox="true"></th>
                                                <th data-field="id"
                                                    data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                                <th data-field="profile"
                                                    data-visible="{{ in_array('profile', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                                    <?= get_label('clients', 'Clients') ?></th>
                                                <th data-field="company"
                                                    data-visible="{{ in_array('company', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true"><?= get_label('company', 'Company') ?></th>
                                                <th data-field="phone"
                                                    data-visible="{{ in_array('phone', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true"><?= get_label('phone_number', 'Phone number') ?></th>

                                                <th data-field="created_at"
                                                    data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                                                <th data-field="updated_at"
                                                    data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                                                <th data-field="actions">
                                                    <?= get_label('actions', 'Actions') ?></th>
                                            </tr>
                                        </thead>

                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    var label_update = 'Update ';
                    var label_delete = 'Delete ';
                    var label_projects = 'Projects ';
                    var label_tasks = 'Tasks ';
                </script>
                <script src="{{asset('front-end/assets/js/pages/clients.js')}}"></script>
                <div class="modal fade" id="create_status_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event"
                            action="" method="POST">

                            <input type="hidden" name="dnr">

                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create status </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <input type="hidden" name="_token" value="JGBo8EvfwiAkbRoazhW9eG7iXuVvWqkXMaNlMpRS"
                                autocomplete="off">
                            <div class="modal-body">

                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Title <span
                                                class="asterisk">*</span></label>
                                        <input type="text" id="nameBasic" class="form-control" name="title"
                                            placeholder="Please enter title" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">COLOR <span
                                                class="asterisk">*</span></label>
                                        <select class="form-select" id="color" name="color">
                                            <option class="badge bg-label-primary" value="primary">
                                                Primary </option>
                                            <option class="badge bg-label-secondary" value="secondary">
                                                Secondary</option>
                                            <option class="badge bg-label-success" value="success">
                                                Success</option>
                                            <option class="badge bg-label-danger" value="danger">
                                                Danger</option>
                                            <option class="badge bg-label-warning" value="warning">
                                                Warning</option>
                                            <option class="badge bg-label-info" value="info">Info </option>
                                            <option class="badge bg-label-dark" value="dark">Dark </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Roles Can Set the Status <i
                                                class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                                data-bs-offset="0,4" data-bs-placement="top" title=""
                                                data-bs-original-title="Including Admin and Roles with All Data Access Permission, Users/Clients Under Selected Role(s) Will Have Permission to Set This Status."></i></label>
                                        <div class="input-group">
                                            <select class="form-control js-example-basic-multiple" name="role_ids[]"
                                                multiple="multiple" data-placeholder="Type to search">
                                                <option value="21">client</option>
                                                <option value="9">member</option>
                                            </select>
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
                                <button type="submit" class="btn btn-danger"
                                    id="confirmDeleteSelections">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>

                @include('layout.labels')
            </div>
            <!-- Footer -->
            @include('layout.footer_bottom')
        </div>
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
