@extends('admin.layouts.app')

@section('title', __('admin.coupons'))

@section('content')
<div class="coupons-header">
    <h1>{{ __('admin.coupons') }}</h1>
    <div>
        <a href="{{ route('admin.coupons.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> {{ __('admin.add_coupon') }}
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
<div class="coupons-filters">
    <form method="GET" action="{{ route('admin.coupons.index') }}" class="filter-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="{{ __('admin.search_coupons') }}" value="{{ request('search') }}">
        </div>
        <div class="filter-group">
            <select name="status">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="discount_type">
                <option value="">{{ __('admin.all_types') }}</option>
                <option value="percentage" {{ request('discount_type') == 'percentage' ? 'selected' : '' }}>{{ __('admin.percentage') }}</option>
                <option value="fixed" {{ request('discount_type') == 'fixed' ? 'selected' : '' }}>{{ __('admin.fixed_amount') }}</option>
            </select>
        </div>
        <button type="submit" class="btn-primary btn-sm">{{ __('admin.filter') }}</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn-secondary btn-sm">{{ __('admin.reset') }}</a>
    </form>
</div>

<!-- Bulk Delete Form -->
<form id="bulkForm" method="POST" action="{{ route('admin.coupons.bulk-delete') }}">
    @csrf
    @method('DELETE')
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                 <tr>
                    <th width="40"><input type="checkbox" id="selectAll"></th>
                    <th>{{ __('admin.id') }}</th>
                    <th>{{ __('admin.code') }}</th>
                    <th>{{ __('admin.discount') }}</th>
                    <th>{{ __('admin.min_order') }}</th>
                    <th>{{ __('admin.valid_from_short') }}</th>
                    <th>{{ __('admin.valid_to_short') }}</th>
                    <th>{{ __('admin.active') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                 </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $coupon->id }}" class="coupon-checkbox"></td>
                    <td>{{ $coupon->id }}</td>
                    <td><strong>{{ $coupon->code }}</strong></td>
                    <td>
                        @if($coupon->discount_type == 'percentage')
                            {{ $coupon->discount_value }}%
                        @else
                            {{ format_currency($coupon->discount_value) }}
                        @endif
                    </td>
                    <td>{{ format_currency($coupon->min_order_amount ?? 0) }}</td>
                    <td>{{ $coupon->valid_from ? $coupon->valid_from->format('Y-m-d') : '-' }}</td>
                    <td>{{ $coupon->valid_to ? $coupon->valid_to->format('Y-m-d') : '-' }}</td>
                    <td>
                        <span class="badge {{ $coupon->active ? 'status-active' : 'status-inactive' }}">
                            {{ $coupon->active ? __('admin.active') : __('admin.inactive') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn-sm btn-edit">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                        <button type="button" class="btn-sm btn-delete delete-single-btn" data-id="{{ $coupon->id }}" data-code="{{ $coupon->code }}">
                            <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('admin.no_coupons_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="pagination-container">
    {{ $coupons->appends(request()->query())->links() }}
</div>

<!-- Hidden form for single delete -->
<form id="singleDeleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .coupons-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .coupons-filters {
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
    const checkboxes = document.querySelectorAll('.coupon-checkbox');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const bulkForm = document.getElementById('bulkForm');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.coupon-checkbox:checked').length;
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
            const checked = document.querySelectorAll('.coupon-checkbox:checked').length;
            if (checked === 0) return;
            if (confirm(`{{ __('admin.bulk_delete_confirm') }}`.replace(':count', checked))) {
                bulkForm.submit();
            }
        });
    }

    // Single delete confirmation
    const deleteBtns = document.querySelectorAll('.delete-single-btn');
    const singleDeleteForm = document.getElementById('singleDeleteForm');
    const baseDeleteUrl = "{{ route('admin.coupons.destroy', ['coupon' => '__id__']) }}";

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const code = this.getAttribute('data-code');
            if (confirm(`{{ __('admin.delete_coupon_confirm') }}: ${code}?`)) {
                singleDeleteForm.action = baseDeleteUrl.replace('__id__', id);
                singleDeleteForm.submit();
            }
        });
    });
</script>
@endpush