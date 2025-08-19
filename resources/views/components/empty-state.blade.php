{{-- Empty State Component --}}
{{-- Usage: @include('components.empty-state', ['icon' => 'bx-task', 'title' => 'No tasks found', 'message' => 'Create your first task to get started']) --}}

<div class="empty-state-container">
    <div class="empty-state text-center py-5">
        <div class="empty-state-icon mb-4">
            <i class="bx {{ $icon ?? 'bx-folder-open' }} display-1 text-muted"></i>
        </div>
        
        <h5 class="mb-2">{{ $title ?? 'No data found' }}</h5>
        
        @if(isset($message))
        <p class="text-muted mb-4">{{ $message }}</p>
        @endif
        
        @if(isset($action))
        <div class="empty-state-action">
            {{ $action }}
        </div>
        @endif
    </div>
</div>

<style>
.empty-state-container {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    max-width: 400px;
    margin: 0 auto;
    animation: fadeIn var(--transition-slow);
}

.empty-state-icon {
    position: relative;
}

.empty-state-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120px;
    height: 120px;
    background: var(--gray-100);
    border-radius: 50%;
    z-index: -1;
}

.empty-state-icon i {
    color: var(--gray-400);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.empty-state h5 {
    color: var(--gray-700);
    font-weight: 600;
}

.empty-state p {
    color: var(--gray-500);
    font-size: 0.875rem;
}

/* Dark mode support */
body.dark-mode .empty-state-icon::before {
    background: var(--gray-200);
}

body.dark-mode .empty-state h5 {
    color: var(--gray-300);
}

body.dark-mode .empty-state p {
    color: var(--gray-400);
}
</style>
