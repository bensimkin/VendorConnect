@include('layout.header')

<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layout.sidebar')

        <!-- Layout container -->
        <div class="layout-page">
            @include('layout.navbar')

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
                                        {{ $title }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            @php
                                $permissions = session('permissions');
                             @endphp
                            @if (in_array('create_task_brief_templates', $permissions))
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_status_modal">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" title="Create task Brief Template">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Meetings -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                {{-- <input type="hidden" id="data_type" value="status"> --}}
                                <table id="table"
                                       data-toggle="table"
                                       data-loading-template="loadingTemplate"
                                       data-url="{{ route('task.brief.template.list') }}"
                                       data-icons-prefix="bx"
                                       data-icons="icons"
                                       data-show-refresh="true"
                                       data-total-field="total"
                                       data-trim-on-search="false"
                                       data-data-field="rows"
                                       data-page-list="[5, 10, 20, 50, 100, 200]"
                                       data-search="false"
                                       data-side-pagination="server"
                                       data-show-columns="true"
                                       data-pagination="true"
                                       data-sort-name="id"
                                       data-sort-order="desc"
                                       data-mobile-responsive="true"
                                       data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            {{-- <th data-checkbox="true"></th> --}}
                                            <th data-sortable="true" data-field="id">{{ get_label('id', 'ID') }}</th>
                                            <th data-sortable="true" data-field="task_type">{{ get_label('task_type', 'Task Type') }}</th>
                                            <th data-sortable="true" data-field="template_name">{{ get_label('template_name', 'Template Name') }}</th>
                                            {{-- <th data-formatter="actionsFormatter">{{ get_label('actions', 'Actions') }}</th> --}}
                                            <th data-field="actions">{{ get_label('actions', 'Actions') }}</th>

                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    var label_update = '{{ get_label('update', 'Update') }}';
                    var label_delete = '{{ get_label('delete', 'Delete') }}';
                </script>
                <script src="{{ asset('front-end/assets/js/pages/taskbrieftemplate.js') }}"></script>

                <!-- Create Task Brief Template Modal -->
                <div class="modal fade" id="create_status_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event" action="{{ route('task.brief.store') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create Task Brief Template</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label class="form-label" for="role">Task Type <span class="asterisk">*</span></label>
                                        <select class="form-select text-capitalize js-example-basic-multiple" id="task_brief_template" name="task_template_id">
                                            <option value="">Please select</option>
                                            @foreach ($task_types as $types)
                                            <option value="{{ $types->id }}">{{ $types->task_type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="nameBasic" class="form-label">Template Name <span class="asterisk">*</span></label>
                                        <input type="text" id="nameBasic" class="form-control" name="template_name" placeholder="Please enter template name" />
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

                <!-- Update Status Modal -->
                <div class="modal fade" id="edit_brief_template" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event" method="POST" action="{{ route('task.brief.update') }}">
                            @csrf
                            <input type="hidden" id="template_id" name="template_id" required />
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Update Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Template Name <span class="asterisk">*</span></label>
                                        <input type="text" id="template_name" class="form-control" name="template_name" placeholder="Please enter template name" required />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-3">
                                        <label class="form-label" for="role">Task Type <span class="asterisk">*</span></label>
                                        <select class="form-select text-capitalize js-example-basic-multiple" id="task_brief_templates" name="task_template_id" required>
                                            <option value="">Please select</option>
                                            @foreach ($task_types as $types)
                                            <option value="{{ $types->id }}">{{ $types->task_type }}</option>
                                            @endforeach
                                        </select>
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
                                <h5 class="modal-title" id="exampleModalLabel2">Warning</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-danger" id="confirmDelete">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>

                @include('layout.labels')
            </div>
            <!-- Content wrapper end -->

            <!-- Footer -->
            @include('layout.footer_bottom')
        </div>
        <!-- Layout page end -->
    </div>
    <!-- Layout container end -->
</div>
<!-- Layout wrapper end -->

<div class="layout-overlay layout-menu-toggle"></div>

@include('layout.footer_links')
</body>
</html>
