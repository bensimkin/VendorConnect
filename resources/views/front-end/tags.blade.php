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
                                        <a href="#">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">
                                        {{$title}}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            @php
                                $permissions = session('permissions');
                            @endphp
                            @if (in_array('create_tags', $permissions))
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_tag_modal">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right" title="Create Tags">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a>
                            @endif
                        </div>
                    </div>
                    <!-- Tags Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                    data-url="{{ route('tags.list') }}" data-icons-prefix="bx" data-icons="icons"
                                    data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                    data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                                    data-search="true" data-side-pagination="server" data-show-columns="true"
                                    data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                    data-mobile-responsive="true" data-query-params="queryParams"
                                    data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}">
                                    <thead>
                                        <tr>
                                            <th data-checkbox="true"></th>
                                            <th data-sortable="true" data-field="id">{{ get_label('id', 'ID') }}</th>
                                            <th data-sortable="true" data-field="tags">{{ get_label('tags', 'Tags') }}</th>
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

                <script src="{{ asset('front-end/assets/js/pages/tags.js') }}"></script>

                <!-- Create Tag Modal -->
                <div class="modal fade" id="create_tag_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form class="modal-content form-submit-event" action="{{ route('tags.store') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Create Tags</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="nameBasic" class="form-label">Tags <span class="asterisk">*</span></label>
                                        <input type="text" id="nameBasic" class="form-control" name="add_tags" placeholder="Please enter tag" required />
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

                <!-- Edit Tag Modal -->
                <div class="modal fade" id="edit_tag_modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('tags.update')}}" class="modal-content form-submit-event" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="tag_id">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel1">Update Tag</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="update_tag" class="form-label">Tag <span class="asterisk">*</span></label>
                                        <input type="text" id="update_tag" class="form-control" name="add_tag" placeholder="Please enter title" required />
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
                                <button type="button" class="btn btn-danger" id="confirmDelete">Yes</button>
                            </div>
                        </div>
                    </div>
                </div>

                @include('layout.labels')

            </div>
            <!-- Footer -->
            @include('layout.footer_bottom')
        </div>
    </div>
    <!-- Layout Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

@include('layout.footer_links')

</body>
</html>
