{{-- Modern Dashboard Card Component --}}
{{-- Usage: @include('components.dashboard-card', ['title' => 'Tasks', 'count' => $taskCount, 'icon' => 'bx-briefcase-alt-2', 'color' => 'primary', 'link' => route('task.view')]) --}}

<div class="col-lg-3 col-md-6 col-12 mb-4">
    <a href="{{ $link }}" class="text-decoration-none">
        <div class="stats-card {{ $color }} fade-in">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 text-uppercase small fw-semibold">{{ $title }}</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($count) }}</h3>
                        @if(isset($trend))
                        <div class="mt-2">
                            <span class="badge badge-{{ $trend > 0 ? 'success' : 'danger' }}">
                                <i class="bx {{ $trend > 0 ? 'bx-trending-up' : 'bx-trending-down' }}"></i>
                                {{ abs($trend) }}%
                            </span>
                            <span class="text-muted small ms-1">vs last month</span>
                        </div>
                        @endif
                    </div>
                    <div class="avatar">
                        <i class="bx {{ $icon }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
