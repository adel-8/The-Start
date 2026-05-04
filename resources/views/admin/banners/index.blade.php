@extends('admin.layouts.app')

@section('title', __('admin.banners'))

@section('content')
<div class="banners-header">
    <h1>{{ __('admin.banners') }}</h1>
    <div>
        <a href="{{ route('admin.banners.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i> {{ __('admin.add_banner') }}
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
<div class="banners-filters">
    <form method="GET" action="{{ route('admin.banners.index') }}" class="filter-form">
        <div class="filter-group">
            <input type="text" name="search" placeholder="{{ __('admin.search_banners') }}" value="{{ request('search') }}">
        </div>
        <div class="filter-group">
            <select name="status">
                <option value="">{{ __('admin.all_statuses') }}</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="device_type">
                <option value="">{{ __('admin.all_devices') }}</option>
                <option value="all" {{ request('device_type') == 'all' ? 'selected' : '' }}>{{ __('admin.all') }}</option>
                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>{{ __('admin.mobile') }}</option>
                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>{{ __('admin.desktop') }}</option>
            </select>
        </div>
        <button type="submit" class="btn-primary btn-sm">{{ __('admin.filter') }}</button>
        <a href="{{ route('admin.banners.index') }}" class="btn-secondary btn-sm">{{ __('admin.reset') }}</a>
    </form>
</div>

<!-- Bulk Delete Form -->
<form id="bulkForm" method="POST" action="{{ route('admin.banners.bulk-delete') }}">
    @csrf
    @method('DELETE')
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="40"><input type="checkbox" id="selectAll"></th>
                    <th>{{ __('admin.id') }}</th>
                    <th>{{ __('admin.image') }}</th>
                    <th>{{ __('admin.title') }}</th>
                    <th>{{ __('admin.link') }}</th>
                    <th>{{ __('admin.position') }}</th>
                    <th>{{ __('admin.status') }}</th>
                    <th>{{ __('admin.schedule') }}</th>
                    <th>{{ __('admin.device') }}</th>
                    <th>{{ __('admin.clicks') }}</th>
                    <th>{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $banner->id }}" class="banner-checkbox"></td>
                    <td>{{ $banner->id }}</td>
                    <td>
                        @if($banner->image_url)
                            <img src="{{ asset($banner->image_url) }}" class="banner-thumb" alt="{{ $banner->title }}">
                        @else
                            <span class="no-image">{{ __('admin.no_image') }}</span>
                        @endif
                    </td>
                    <td>{{ $banner->title ?? '-' }}</td>
                    <td>
                        @if($banner->link)
                            <a href="{{ $banner->link }}" target="_blank" rel="noopener">{{ Str::limit($banner->link, 40) }}</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div class="position-controls">
                            @if($banner->position > 0)
                                <a href="{{ route('admin.banners.move', ['banner' => $banner, 'direction' => 'up']) }}" class="btn-sm btn-icon"><i class="fas fa-arrow-up"></i></a>
                            @endif
                            @if(!$loop->last)
                                <a href="{{ route('admin.banners.move', ['banner' => $banner, 'direction' => 'down']) }}" class="btn-sm btn-icon"><i class="fas fa-arrow-down"></i></a>
                            @endif
                            <span>{{ $banner->position }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $banner->status ? 'status-active' : 'status-inactive' }}">
                            {{ $banner->status ? __('admin.active') : __('admin.inactive') }}
                        </span>
                    </td>
                    <td>
                        @if($banner->starts_at || $banner->ends_at)
                            {{ $banner->starts_at ? $banner->starts_at->format('Y-m-d') : '∞' }} → 
                            {{ $banner->ends_at ? $banner->ends_at->format('Y-m-d') : '∞' }}
                        @else
                            {{ __('admin.always') }}
                        @endif
                    </td>
                    <td>
                        @if($banner->device_type == 'all')
                            {{ __('admin.all') }}
                        @else
                            {{ __('admin.' . $banner->device_type) }}
                        @endif
                    </td>
                    <td>{{ number_format($banner->clicks) }}</td>
                    <td>
                        <a href="{{ route('admin.banners.edit', $banner) }}" class="btn-sm btn-edit">
                            <i class="fas fa-edit"></i> {{ __('admin.edit') }}
                        </a>
                        <button type="button" class="btn-sm btn-delete delete-single-btn" data-id="{{ $banner->id }}" data-title="{{ $banner->title }}">
                            <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center">{{ __('admin.no_banners_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="pagination-container">
    {{ $banners->appends(request()->query())->links() }}
</div>

<form id="singleDeleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .banners-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .banners-filters { background: var(--color-surface); padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border: 1px solid var(--color-border); }
    .filter-form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
    .filter-group input, .filter-group select { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: 0.5rem; background: var(--color-surface); min-width: 150px; }
    .table-responsive { overflow-x: auto; margin-bottom: 1.5rem; }
    .admin-table { min-width: 1000px; width: 100%; }
    .banner-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 0.5rem; background: var(--color-background); }
    .position-controls { display: flex; align-items: center; gap: 0.5rem; }
    .btn-sm { padding: 0.25rem 0.6rem; font-size: 0.75rem; border-radius: 0.5rem; display: inline-flex; align-items: center; gap: 0.3rem; text-decoration: none; cursor: pointer; border: none; }
    .btn-icon { background: var(--color-surface); border: 1px solid var(--color-border); color: var(--color-text); }
    .btn-icon:hover { background: var(--color-primary); color: white; }
    .btn-edit { background: var(--color-info); color: white; }
    .btn-delete { background: var(--color-danger); color: white; }
    .status-active { background: rgba(16,185,129,0.1); color: #10B981; }
    .status-inactive { background: rgba(239,68,68,0.1); color: #EF4444; }
    .text-center { text-align: center; }
    @media (max-width: 768px) {
        .filter-form { flex-direction: column; align-items: stretch; }
        .filter-group input, .filter-group select { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
    // Select All, Bulk, Single Delete (same as before)
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.banner-checkbox');
    const bulkBtn = document.getElementById('bulkDeleteBtn');
    const bulkForm = document.getElementById('bulkForm');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.banner-checkbox:checked').length;
        bulkBtn.disabled = checked === 0;
    }
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkButton));

    if (bulkBtn) {
        bulkBtn.addEventListener('click', function() {
            const checked = document.querySelectorAll('.banner-checkbox:checked').length;
            if (checked === 0) return;
            if (confirm(`{{ __('admin.bulk_delete_confirm') }}`.replace(':count', checked))) {
                bulkForm.submit();
            }
        });
    }

    const deleteBtns = document.querySelectorAll('.delete-single-btn');
    const singleDeleteForm = document.getElementById('singleDeleteForm');
    const baseDeleteUrl = "{{ route('admin.banners.destroy', ['banner' => '__id__']) }}";
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            if (confirm(`{{ __('admin.delete_banner_confirm') }}: ${title}?`)) {
                singleDeleteForm.action = baseDeleteUrl.replace('__id__', id);
                singleDeleteForm.submit();
            }
        });
    });
</script>
@endpush