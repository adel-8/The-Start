@extends('admin.layouts.app')

@section('title', __('admin.manage_products'))

@section('content')
<div class="products-header">
    <h1>{{ __('admin.products') }}</h1>
    <a href="{{ route('admin.products.create') }}" class="btn-primary">
        <i class="fas fa-plus"></i> {{ __('admin.add_new_product') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Filter Bar -->
<div class="products-filters">
    <form method="GET" action="{{ route('admin.products.index') }}" class="filter-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="{{ __('admin.search_products') }}" value="{{ request('search') }}">
        </div>
        <div class="filter-group">
            <select name="status">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="stock_status">
                <option value="">{{ __('admin.all_stock') }}</option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>{{ __('admin.low_stock') }}</option>
                <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>{{ __('admin.out_of_stock') }}</option>
            </select>
        </div>
        <button type="submit" class="btn-primary btn-sm">{{ __('admin.filter') }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary btn-sm">{{ __('admin.reset') }}</a>
    </form>
</div>

<!-- Bulk Actions -->
<div class="bulk-actions">
    <button type="button" id="bulkDeleteBtn" class="btn-danger btn-sm" disabled>
        <i class="fas fa-trash"></i> {{ __('admin.delete_selected') }}
    </button>
    <span id="selectedCount" class="selected-count">0 {{ __('admin.selected') }}</span>
</div>

<!-- Products Table -->
<div class="table-responsive">
    <form id="bulkForm" method="POST" action="{{ route('admin.products.bulk-delete') }}">
        @csrf
        @method('DELETE')
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40"><input type="checkbox" id="selectAll"></th>
                    <th>{{ __('admin.id') }}</th>
                    <th>{{ __('admin.image') }}</th>
                    <th>{{ __('admin.name') }}</th>
                    <th>{{ __('admin.price') }}</th>
                    <th>{{ __('admin.stock') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $product->id }}" class="product-checkbox"></td>
                    <td>{{ $product->id }}</td>
                    <td>
                        @if($product->image_url)
                            <img src="{{ asset($product->image_url) }}" class="product-thumb" alt="{{ $product->name }}">
                        @else
                            <span class="no-image">{{ __('admin.no_image') }}</span>
                        @endif
                    </td>
                    <td>{{ $product->name }}</td>
                    <td>{{ format_currency($product->price) }}</td>
                    <td class="{{ $product->stock <= 5 ? 'stock-warning' : ($product->stock == 0 ? 'stock-out' : '') }}">
                        {{ $product->stock ?? '∞' }}
                        @if($product->stock <= 5 && $product->stock > 0)
                            <span class="stock-badge low">{{ __('admin.low_stock') }}</span>
                        @elseif($product->stock == 0)
                            <span class="stock-badge out">{{ __('admin.out_of_stock') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge product-{{ $product->status }}">
                            {{ __('admin.' . $product->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn-sm btn-edit">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-sm btn-delete" onclick="return confirm('{{ __('admin.delete_product_confirm') }}: {{ $product->name }}?')">
                                <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">{{ __('admin.no_products_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </form>
</div>

<div class="pagination-container">
    {{ $products->appends(request()->query())->links() }}
</div>
@endsection

@push('styles')
<style>
    .products-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .products-filters {
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
    }
    .bulk-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .btn-danger {
        background: var(--color-danger);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        cursor: pointer;
    }
    .btn-danger:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .table-responsive {
        overflow-x: auto;
        margin-bottom: 1.5rem;
    }
    .admin-table {
        min-width: 800px;
        width: 100%;
    }
    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 0.5rem;
    }
    .stock-warning { color: var(--color-warning); font-weight: 500; }
    .stock-out { color: var(--color-danger); font-weight: 500; }
    .stock-badge {
        display: inline-block;
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
        border-radius: 40px;
        margin-left: 0.5rem;
    }
    .stock-badge.low { background: rgba(245,158,11,0.1); color: var(--color-warning); }
    .stock-badge.out { background: rgba(239,68,68,0.1); color: var(--color-danger); }
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
    .btn-edit { background: var(--color-info); color: white; }
    .btn-delete { background: var(--color-danger); color: white; }
    .text-center { text-align: center; }
    @media (max-width: 768px) {
        .filter-form { flex-direction: column; align-items: stretch; }
        .filter-group input, .filter-group select { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Select All
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.product-checkbox:checked').length;
        bulkBtn.disabled = checked === 0;
        selectedCount.textContent = `${checked} {{ __('admin.selected') }}`;
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
            const checked = document.querySelectorAll('.product-checkbox:checked').length;
            if (checked === 0) return;
            if (confirm('{{ __("admin.bulk_delete_confirm") }}'.replace(':count', checked))) {
                document.getElementById('bulkForm').submit();
            }
        });
    }
</script>
@endpush