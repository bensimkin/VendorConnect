{{-- Modern Table Component --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $title ?? 'Table' }}</h5>
        @if(isset($actions))
        <div class="card-actions">
            {{ $actions }}
        </div>
        @endif
    </div>
    <div class="card-body">
        @if(isset($filters))
        <div class="table-filters mb-3">
            {{ $filters }}
        </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-hover modern-table">
                <thead>
                    <tr>
                        {{ $thead }}
                    </tr>
                </thead>
                <tbody>
                    {{ $tbody }}
                </tbody>
            </table>
        </div>
        
        @if(isset($pagination))
        <div class="table-pagination mt-3">
            {{ $pagination }}
        </div>
        @endif
    </div>
</div>

<style>
.modern-table {
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead th {
    background: var(--gray-50);
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    color: var(--gray-600);
    padding: 1rem;
    white-space: nowrap;
}

.modern-table tbody tr {
    transition: all var(--transition-fast);
    border-bottom: 1px solid var(--gray-200);
}

.modern-table tbody tr:hover {
    background: var(--gray-50);
    transform: scale(1.01);
    box-shadow: var(--shadow-sm);
}

.modern-table td {
    padding: 1rem;
    vertical-align: middle;
    border: none;
}

.table-actions .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.table-filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}
</style>
