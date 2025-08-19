@include('layout.header')

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->
        @include('layout.sidebar')

        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->
            @include('layout.navbar')

            <div class="container-fluid">
                <div class="d-flex justify-content-between mb-2 mt-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-style1">
                                <li class="breadcrumb-item">
                                    <a href="#">Home</a>
                                </li>
                                <li class="breadcrumb-item active">
                                    {{ $title }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="user_filter" aria-label="Default select example">
                                    <option value="">Select User</option>
                                    <option value="7" selected>Admin Infinitie</option>
                                    <option value="76">Member2 Infinitie</option>
                                    <option value="77">Member Infinitie</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="client_filter" aria-label="Default select example">
                                    <option value="">Select Client</option>
                                    <option value="66">Client Two</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="type_filter" aria-label="Default select example">
                                    <option value="">Select Type</option>
                                    <option value="project">Project</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="status_filter" aria-label="Default select example">
                                    <option value="">Select Status</option>
                                    <option value="read">Read</option>
                                    <option value="unread">Unread</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive text-nowrap">
                            <input type="hidden" id="data_type" value="notifications">
                            <input type="hidden" id="save_column_visibility">
                            <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                data-url="#" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true"
                                data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-checkbox="true"></th>
                                        <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                                        <th data-sortable="true" data-field="title"><?= get_label('title', 'Title') ?>
                                        </th>
                                        <th data-sortable="true" data-field="message">
                                            <?= get_label('message', 'Message') ?></th>
                                        <th data-field="users" data-formatter="UserFormatter">
                                            <?= get_label('users', 'Users') ?></th>
                                        <th data-field="clients" data-formatter="ClientFormatter">
                                            <?= get_label('clients', 'Clients') ?></th>
                                        <th data-sortable="true" data-field="type"><?= get_label('type', 'Type') ?></th>
                                        <th data-sortable="true" data-field="status">
                                            <?= get_label('status', 'Status') ?></th>
                                        <th data-sortable="true" data-field="created_at" data-visible="false">
                                            <?= get_label('created_at', 'Created at') ?></th>
                                        <th data-sortable="true" data-field="updated_at" data-visible="false">
                                            <?= get_label('updated_at', 'Updated at') ?></th>
                                        <th data-sortable="false" data-field="actions">
                                            <?= get_label('actions', 'Actions') ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <script src="{{ asset('front-end/assets/js/pages/notifications.js') }}"></script>

            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
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
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are You Sure You Want to Delete Selected Record(s)?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-danger" id="confirmDeleteSelections">Yes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="mark_all_notifications_as_read_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are You Sure You Want to Mark All Notifications as Read?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary" id="confirmMarkAllAsRead">Yes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="update_notification_status_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="exampleModalLabel2">Confirm!</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are You Sure You Want to Update Notification Status?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="submit" class="btn btn-primary"
                                id="confirmNotificationStatus">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            @include('layout.footer_bottom')
            <!-- / Footer -->
        </div>
        <!-- / Layout page -->
    </div>
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout wrapper -->


@include('layout.footer_links')
</body>

</html>
