@extends('admin.layouts.app')

@section('title', __('admin.categories'))

@section('content')
<div class="categories-header">
    <h1>{{ __('admin.categories') }}</h1>
    <div>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> {{ __('admin.add_category') }}
        </a>
        <button type="button" id="bulkDeleteBtn" class="btn-danger btn-sm" disabled>
            <i class="fas fa-trash"></i> {{ __('admin.delete_selected') }}
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Filters -->
<div class="categories-filters">
    <form method="GET" action="{{ route('admin.categories.index') }}" class="filter-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="{{ __('admin.search_categories') }}" value="{{ request('search') }}">
        </div>
        <div class="filter-group">
            <select name="status">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
        <button type="submit" class="btn-primary btn-sm">{{ __('admin.filter') }}</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary btn-sm">{{ __('admin.reset') }}</a>
    </form>
</div>

<!-- Bulk Delete Form -->
<form id="bulkForm" method="POST" action="{{ route('admin.categories.bulk-delete') }}">
    @csrf
    @method('DELETE')
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                  <!-- ... headers ... -->
            </thead>
            <tbody>
                @forelse($categories as $category)
                 <!-- ... table row content ... -->
                @empty
                 <!-- ... empty row ... -->
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="pagination-container">
    {{ $categories->appends(request()->query())->links() }}
</div>

<!-- Hidden form for single delete -->
<form id="singleDeleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .categories-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .categories-filters {
        background: var(--color-surface);
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--color-border);
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .filter-group input, .filter-group select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        background: var(--color-surface);
        min-width: 180px;
    }
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1.5rem;
    }
    .admin-table {
        min-width: 800px;
        width: 100%;
    }
    .indent {
        display: inline-block;
        margin-left: 1rem;
        font-size: 0.9rem;
        color: var(--color-muted);
    }
    .position-controls {
        display: flex;
        gap: 0.25rem;
    }
    .btn-sm {
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        text-decoration: none;
        cursor: pointer;
        border: none;
    }
    .btn-icon {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text);
    }
    .btn-icon:hover {
        background: var(--color-primary);
        color: white;
    }
    .btn-edit {
        background: var(--color-info);
        color: white;
    }
    .btn-delete {
        background: var(--color-danger);
        color: white;
    }
    .status-active {
        background: rgba(16,185,129,0.1);
        color: #10B981;
    }
    .status-inactive {
        background: rgba(239,68,68,0.1);
        color: #EF4444;
    }
    .text-center {
        text-align: center;
    }
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-group input, .filter-group select {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Select All
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.category-checkbox');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const bulkForm = document.getElementById('bulkForm');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.category-checkbox:checked').length;
        bulkBtn.disabled = checked === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkButton));

    // Bulk delete confirmation
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.category-checkbox:checked').length;
            if (checked === 0) return;
            if (confirm(`{{ __('admin.bulk_delete_confirm') }}`.replace(':count', checked))) {
                bulkForm.submit();
            }
        });
    }

    // Single delete confirmation
    const deleteBtns = document.querySelectorAll('.delete-single-btn');
    const singleDeleteForm = document.getElementById('singleDeleteForm');
    // Build a base URL with a placeholder
    const baseDeleteUrl = "{{ route('admin.categories.destroy', ['category' => '__id__']) }}";

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            if (confirm(`{{ __('admin.delete_category_confirm') }}: ${name}?`)) {
                singleDeleteForm.action = baseDeleteUrl.replace('__id__', id);
                singleDeleteForm.submit();
            }
        });
    });
</script>
@endpush