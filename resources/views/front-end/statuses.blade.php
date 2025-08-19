@include('layout.header')
<!-- Layout wrapper -->
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
                                        {{ $title }} </li>

                                </ol>
                            </nav>
                        </div>
                        <div>
                            @php
                            $permissions = session('permissions');
                        @endphp
                            @if (in_array('create_statuses', $permissions))
                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                data-bs-target="#create_status_modal"><button type="button"
                                    class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                                    data-bs-original-title=" Create status"><i class="bx bx-plus"></i></button>
                                </a>
                            @endif
                        </div>
                    </div>
                    <!-- meetings -->
                    <div class="card ">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                @csrf
                                <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                    data-url="{{ route('status.list') }}" data-icons-prefix="bx" data-icons="icons"
                                    data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                    data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-query-params="queryParams"
                                    data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}">
                                    <thead>
                                        <tr>
                                            {{-- <th data-checkbox="true"></th> --}}
                                            <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                                            <th data-sortable="true" data-field="statuses">
                                                <?= get_label('statuses', 'Status') ?></th>
                                            {{-- @if (isAdminOrHasAllDataAccess())
                                                <th data-field="roles_has_access" data-visible="true">
                                                    <?= //get_label('allowed_roles', 'Allowed Roles') ?> <i
                                                        class='bx bx-info-circle text-primary'
                                                        title="{{ get_label('roles_can_set_status_info_1', 'Including Admin and Roles with All Data Access Permission, Roles That Can Set This Status.') }}"></i>
                                                </th>
                                            @endif --}}
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

                <script src="{{asset('front-end/assets/js/pages/status.js')}}"></script>

                <div class="modal fade" id="create_status_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event" action="{{ route('status.store') }}"
                            method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create status </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Add statuses <span
                                                class="asterisk">*</span></label>
                                        <input type="text" id="nameBasic" class="form-control" name="title"
                                            placeholder="Please enter statuses" />
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close</label>
                                </button>
                                <button type="submit" class="btn btn-primary" id="submit_btn">Create</label></button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="edit_status_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('status.update') }}" class="modal-content form-submit-event"
                            method="POST">
                            @csrf
                            <input type="hidden" name="id" id="status_id">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Update status </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Update Status <span
                                                class="asterisk">*</span></label>
                                        <input type="text" id="status_title" class="form-control" name="update_status"
                                            placeholder="Please enter status" required />
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
