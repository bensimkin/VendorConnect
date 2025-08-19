{{-- Modern Task Card Component --}}
<div class="card task-card mb-3 fade-in">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="card-title mb-1">{{ $task->title }}</h5>
                <p class="text-muted small mb-0">
                    <i class="bx bx-calendar me-1"></i>
                    Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                </p>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="bx bx-edit me-2"></i>Edit</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bx bx-user-plus me-2"></i>Assign</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#"><i class="bx bx-trash me-2"></i>Delete</a></li>
                </ul>
            </div>
        </div>
        
        <p class="card-text text-muted small">{{ Str::limit($task->description, 100) }}</p>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex gap-2">
                <span class="badge badge-{{ $task->priority->color ?? 'info' }}">
                    {{ $task->priority->title ?? 'Normal' }}
                </span>
                <span class="badge badge-{{ $task->status->color ?? 'secondary' }}">
                    {{ $task->status->title ?? 'Pending' }}
                </span>
            </div>
            
            <div class="avatar-group">
                @foreach($task->users->take(3) as $user)
                <div class="avatar avatar-sm" data-bs-toggle="tooltip" title="{{ $user->first_name }}">
                    @if($user->photo)
                        <img src="{{ $user->photo }}" alt="{{ $user->first_name }}" class="rounded-circle">
                    @else
                        <span class="avatar-initial rounded-circle bg-primary">
                            {{ substr($user->first_name, 0, 1) }}
                        </span>
                    @endif
                </div>
                @endforeach
                @if($task->users->count() > 3)
                <div class="avatar avatar-sm">
                    <span class="avatar-initial rounded-circle bg-secondary">
                        +{{ $task->users->count() - 3 }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
