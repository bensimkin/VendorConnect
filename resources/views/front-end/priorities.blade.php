@include('layout.header')

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')

            <!-- Content wrapper -->
            <div class="container-fluid">
                <div class="d-flex justify-content-between mb-2 mt-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-style1">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard.view') }}">Home</a>
                                </li>
                                <li class="breadcrumb-item active">
                                    {{ $title }}
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        @php
                            $permissions = session('permissions');
                        @endphp
                            @if (in_array('create_priorities', $permissions))
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_priority_modal">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" title="Create Priority">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </a>
                            @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            {{-- <input type="hidden" id="data_type" value="priority"> --}}
                            <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                data-url="{{ route('priority.list') }}" data-icons-prefix="bx" data-icons="icons"
                                data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                                data-search="true" data-side-pagination="server" data-show-columns="true"
                                data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                data-mobile-responsive="true" data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        {{-- <th data-checkbox="true"></th> --}}
                                        <th data-sortable="true" data-field="id">{{ get_label('id', 'ID') }}</th>
                                        <th data-sortable="true" data-field="priority">{{ get_label('priority', 'Priority') }}</th>
                                        <th data-sortable="true" data-field="created_at" data-visible="false">{{ get_label('created_at', 'Created At') }}</th>
                                        <th data-sortable="true" data-field="updated_at" data-visible="false">{{ get_label('updated_at', 'Updated At') }}</th>
                                        <th data-field="actions">{{ get_label('actions', 'Actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <script src="{{ asset('front-end/assets/js/pages/priority.js') }}"></script>

            <!-- Confirm Delete Selected Modal -->
            <div class="modal fade" id="confirmDeleteSelectedModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Warning</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete selected record(s)?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteSelections">Yes</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Priority Modal -->
            <div class="modal fade" id="create_priority_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form class="modal-content form-submit-event" action="{{ route('priority.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Create Priority</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="nameBasic" class="form-label">Priority <span class="asterisk">*</span></label>
                                    <input type="text" id="nameBasic" class="form-control" name="title" placeholder="Please enter priority" required />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Priority Modal -->
            <div class="modal fade" id="edit_priority_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form action="{{ route('priority.update') }}" class="modal-content form-submit-event" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="priority_id">
                        <div class="modal-header">
                            <h5 class="modal-title">Update Priority</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col mb-3">
                                    <label for="priority_title" class="form-label">Priority <span class="asterisk">*</span></label>
                                    <input type="text" id="priority_title" class="form-control" name="title" placeholder="Please enter priority" required />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Warning</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this record?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Yes</button>
                        </div>
                    </div>
                </div>
            </div>

            @include('layout.labels')

            @include('layout.footer_bottom')
        </div>
        <!-- Footer -->
    </div>
    <!-- / footer -->
</div>


<!-- Overlay -->
<div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout page -->

@include('layout.footer_links')

</body>
</html>
