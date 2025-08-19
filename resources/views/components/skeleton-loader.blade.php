{{-- Skeleton Loader Component --}}
{{-- Usage: @include('components.skeleton-loader', ['type' => 'card']) --}}

@if($type === 'card')
<div class="card skeleton-card">
    <div class="card-body">
        <div class="skeleton-line heading mb-3"></div>
        <div class="skeleton-line"></div>
        <div class="skeleton-line short"></div>
        <div class="d-flex justify-content-between mt-3">
            <div class="skeleton-badge"></div>
            <div class="skeleton-avatar"></div>
        </div>
    </div>
</div>
@elseif($type === 'table-row')
<tr class="skeleton-row">
    <td><div class="skeleton-line"></div></td>
    <td><div class="skeleton-line short"></div></td>
    <td><div class="skeleton-badge"></div></td>
    <td><div class="skeleton-line short"></div></td>
</tr>
@elseif($type === 'dashboard-card')
<div class="col-lg-3 col-md-6 col-12 mb-4">
    <div class="card skeleton-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="skeleton-line short mb-2"></div>
                    <div class="skeleton-line heading"></div>
                </div>
                <div class="skeleton-avatar"></div>
            </div>
        </div>
    </div>
</div>
@else
<div class="skeleton-line"></div>
@endif

<style>
@keyframes skeleton-loading {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.skeleton-line,
.skeleton-badge,
.skeleton-avatar {
    background: linear-gradient(
        90deg,
        var(--gray-200) 0px,
        var(--gray-100) 40px,
        var(--gray-200) 80px
    );
    background-size: 200px 100%;
    animation: skeleton-loading 1.5s ease-in-out infinite;
    border-radius: var(--radius-sm);
}

.skeleton-line {
    height: 1rem;
    margin-bottom: 0.5rem;
    width: 100%;
}

.skeleton-line.heading {
    height: 1.5rem;
    width: 40%;
}

.skeleton-line.short {
    width: 60%;
}

.skeleton-badge {
    height: 1.5rem;
    width: 4rem;
    border-radius: var(--radius-sm);
}

.skeleton-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-md);
}

.skeleton-card {
    min-height: 120px;
}

.skeleton-row td {
    padding: 1rem;
}

/* Dark mode support */
body.dark-mode .skeleton-line,
body.dark-mode .skeleton-badge,
body.dark-mode .skeleton-avatar {
    background: linear-gradient(
        90deg,
        var(--gray-300) 0px,
        var(--gray-200) 40px,
        var(--gray-300) 80px
    );
}
</style>
