@extends('admin.layouts.app')

@section('title', __('admin.categories'))

@section('content')

<div class="categories-header">
    <h1>{{ __('admin.categories') }}</h1>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> {{ __('admin.add_category') }}
        </a>
        <button type="button" id="bulkDeleteBtn" class="btn-sm btn-delete" disabled>
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
            <input type="text"
                   name="search"
                   placeholder="{{ __('admin.search_categories') }}"
                   value="{{ request('search') }}">
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
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="selectAll" title="{{ __('admin.select_all') }}">
                    </th>
                    <th>{{ __('admin.id') }}</th>
                    <th>{{ __('admin.image') }}</th>
                    <th>{{ __('admin.name') }}</th>
                    <th>{{ __('admin.slug') }}</th>
                    <th>{{ __('admin.parent') }}</th>
                    <th>{{ __('admin.products_count') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.position') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>
                        <input type="checkbox"
                               name="ids[]"
                               value="{{ $category->id }}"
                               class="category-checkbox">
                    </td>
                    <td>{{ $category->id }}</td>
                    <td>
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}"
                                 alt="{{ $category->name }}"
                                 style="width:40px; height:40px; object-fit:cover; border-radius:0.5rem;">
                        @else
                            <div style="width:40px; height:40px; background:var(--color-border); border-radius:0.5rem; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-image" style="color:var(--color-muted); font-size:0.8rem;"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($category->parent_id)
                            <span class="indent">↳ </span>
                        @endif
                        {{ $category->name }}
                    </td>
                    <td style="color:var(--color-muted); font-size:0.85rem;">{{ $category->slug }}</td>
                    <td>{{ $category->parent->name ?? '—' }}</td>
                    <td style="text-align:center;">
                        {{ $category->products_count ?? $category->products()->count() }}
                    </td>
                    <td>
                        @if($category->is_active ?? $category->status)
                            <span class="badge status-active">{{ __('admin.active') }}</span>
                        @else
                            <span class="badge status-inactive">{{ __('admin.inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="position-controls">
                            <a href="{{ route('admin.categories.move', ['category' => $category->id, 'direction' => 'up']) }}"
                               class="btn-sm btn-icon" title="{{ __('admin.move_up') }}">
                                <i class="fas fa-arrow-up"></i>
                            </a>
                            <a href="{{ route('admin.categories.move', ['category' => $category->id, 'direction' => 'down']) }}"
                               class="btn-sm btn-icon" title="{{ __('admin.move_down') }}">
                                <i class="fas fa-arrow-down"></i>
                            </a>
                        </div>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.categories.edit', $category) }}"
                           class="btn-sm btn-edit">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                        <button type="button"
                                class="btn-sm btn-delete delete-single-btn"
                                data-id="{{ $category->id }}"
                                data-name="{{ $category->name }}">
                            <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:2rem; color:var(--color-muted);">
                        {{ __('admin.no_categories') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div style="margin-top:1.5rem;">
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
    .filter-group input,
    .filter-group select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        background: var(--color-surface);
        min-width: 180px;
        font-size: 0.9rem;
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 1rem;
        border: 1px solid var(--color-border);
    }
    .admin-table {
        min-width: 900px;
        width: 100%;
        border: none;
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
    .btn-icon {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text);
        padding: 0.25rem 0.5rem;
        border-radius: 0.4rem;
        font-size: 0.75rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .btn-icon:hover {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }
    .btn-edit {
        background: var(--color-info);
        color: white;
        padding: 0.25rem 0.6rem;
        border-radius: 0.4rem;
        font-size: 0.75rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .btn-delete {
        background: var(--color-danger);
        color: white;
        border: none;
        cursor: pointer;
        padding: 0.25rem 0.6rem;
        border-radius: 0.4rem;
        font-size: 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .status-active {
        background: rgba(16,185,129,0.1);
        color: #10B981;
        padding: 0.2rem 0.6rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .status-inactive {
        background: rgba(239,68,68,0.1);
        color: #EF4444;
        padding: 0.2rem 0.6rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-group input,
        .filter-group select {
            width: 100%;
            min-width: unset;
        }
        .categories-header h1 {
            font-size: 1.2rem;
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