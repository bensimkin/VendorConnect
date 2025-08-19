{{-- Example: Modern Tasks Page --}}
{{-- This is an example of how to use the modern components --}}

@include('layout.header')
@include('layout.sidebar')

<div class="layout-page">
    @include('layout.navbar')
    
    <div class="content-wrapper">
        <div class="container-fluid flex-grow-1 container-p-y">
            {{-- Page Header --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4 class="mb-0">Tasks Management</h4>
                    <p class="text-muted">Manage and track all your tasks</p>
                </div>
                <div class="col-md-6 text-end">
                    @include('components.modern-button', [
                        'text' => 'Create Task',
                        'icon' => 'bx-plus',
                        'variant' => 'primary',
                        'onclick' => "window.location.href='" . route('task.create') . "'"
                    ])
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row mb-4">
                @include('components.dashboard-card', [
                    'title' => 'Total Tasks',
                    'count' => 156,
                    'icon' => 'bx-task',
                    'color' => 'primary',
                    'link' => '#',
                    'trend' => 12
                ])
                
                @include('components.dashboard-card', [
                    'title' => 'In Progress',
                    'count' => 43,
                    'icon' => 'bx-loader',
                    'color' => 'warning',
                    'link' => '#',
                    'trend' => -5
                ])
                
                @include('components.dashboard-card', [
                    'title' => 'Completed',
                    'count' => 98,
                    'icon' => 'bx-check-circle',
                    'color' => 'success',
                    'link' => '#',
                    'trend' => 23
                ])
                
                @include('components.dashboard-card', [
                    'title' => 'Overdue',
                    'count' => 15,
                    'icon' => 'bx-time-five',
                    'color' => 'danger',
                    'link' => '#',
                    'trend' => -8
                ])
            </div>

            {{-- Tasks Table --}}
            @component('components.modern-table', ['title' => 'Recent Tasks'])
                @slot('actions')
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                            <i class="bx bx-filter me-1"></i> Filter
                        </button>
                        <button class="btn btn-sm btn-light">
                            <i class="bx bx-export me-1"></i> Export
                        </button>
                    </div>
                @endslot

                @slot('filters')
                    <div class="row g-3">
                        <div class="col-md-3">
                            @include('components.form-input', [
                                'name' => 'search',
                                'placeholder' => 'Search tasks...',
                                'icon' => 'bx-search'
                            ])
                        </div>
                        <div class="col-md-3">
                            @include('components.form-input', [
                                'name' => 'status',
                                'type' => 'select',
                                'options' => ['all' => 'All Status', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed']
                            ])
                        </div>
                        <div class="col-md-3">
                            @include('components.form-input', [
                                'name' => 'priority',
                                'type' => 'select',
                                'options' => ['all' => 'All Priority', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low']
                            ])
                        </div>
                        <div class="col-md-3">
                            @include('components.modern-button', [
                                'text' => 'Apply Filters',
                                'icon' => 'bx-check',
                                'variant' => 'primary',
                                'class' => 'w-100'
                            ])
                        </div>
                    </div>
                @endslot

                @slot('thead')
                    <th>
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </th>
                    <th>Task</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                @endslot

                @slot('tbody')
                    {{-- Example task rows --}}
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input">
                        </td>
                        <td>
                            <div>
                                <h6 class="mb-0">Design new landing page</h6>
                                <small class="text-muted">Create mockups for the new marketing site</small>
                            </div>
                        </td>
                        <td>
                            <div class="avatar-group">
                                <div class="avatar avatar-sm" data-bs-toggle="tooltip" title="John Doe">
                                    <span class="avatar-initial rounded-circle bg-primary">JD</span>
                                </div>
                                <div class="avatar avatar-sm" data-bs-toggle="tooltip" title="Jane Smith">
                                    <span class="avatar-initial rounded-circle bg-success">JS</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-warning">High</span>
                        </td>
                        <td>
                            <span class="badge badge-info">In Progress</span>
                        </td>
                        <td>
                            <span class="text-muted">Dec 25, 2024</span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i class="bx bx-show me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#"><i class="bx bx-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    {{-- Loading skeleton example --}}
                    <tr class="d-none" id="loadingRows">
                        @for($i = 0; $i < 3; $i++)
                        @include('components.skeleton-loader', ['type' => 'table-row'])
                        @endfor
                    </tr>
                @endslot

                @slot('pagination')
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                @endslot
            @endcomponent

            {{-- Empty State Example (uncomment to see) --}}
            {{-- 
            @include('components.empty-state', [
                'icon' => 'bx-task',
                'title' => 'No tasks found',
                'message' => 'Create your first task to get started with task management',
                'action' => '<button class="btn btn-primary"><i class="bx bx-plus me-2"></i>Create First Task</button>'
            ])
            --}}
        </div>
    </div>
</div>

@include('layout.footer_links')

<script>
// Example: Loading state demo
function showLoadingState() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
    button.disabled = true;
    
    // Show skeleton loaders
    document.getElementById('loadingRows').classList.remove('d-none');
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        document.getElementById('loadingRows').classList.add('d-none');
        showNotification('Data loaded successfully!', 'success');
    }, 2000);
}

// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>

</body>
</html>
