@extends('admin.layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', __('admin.manage_reviews'))

@section('content')

<div class="admin-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <h1>{{ __('admin.product_reviews') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>{{ __('admin.id') }}</th>
                <th>{{ __('admin.product') }}</th>
                <th>{{ __('admin.user') }}</th>
                <th>{{ __('admin.rating') }}</th>
                <th>{{ __('admin.comment') }}</th>
                <th>{{ __('admin.status') }}</th>
                <th>{{ __('admin.date') }}</th>
                <th>{{ __('admin.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $review)
            <tr>
                <td>{{ $review->id }}</td>
                <td>
                    <a href="{{ route('product.show', $review->product->slug) }}" target="_blank">
                        {{ $review->product->name }}
                    </a>
                </td>
                <td>{{ $review->user->name ?? $review->user->username ?? __('admin.unknown') }}</td>
                <td>
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating) ★ @else ☆ @endif
                        @endfor
                    </div>
                </td>
                <td>{{ Str::limit($review->comment, 60) }}</td>
                <td>
                    @if($review->approved)
                        <span class="badge badge-success">{{ __('admin.approved') }}</span>
                    @else
                        <span class="badge badge-warning">{{ __('admin.pending') }}</span>
                    @endif
                </td>
                <td>{{ $review->created_at->format('Y-m-d') }}</td>
                <td style="white-space:nowrap;">
                    @if(!$review->approved)
                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn-sm btn-success">
                                {{ __('admin.approve') }}
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn-sm btn-delete delete-review-btn"
                                data-confirm="{{ __('admin.delete_review_confirm') }}">
                            {{ __('admin.delete') }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding:2rem; color:var(--color-muted);">
                    {{ __('admin.no_reviews') }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1.5rem;">
    {{ $reviews->links() }}
</div>

@endsection

@push('styles')
<style>
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 1rem;
        border: 1px solid var(--color-border);
    }
    .admin-table {
        min-width: 850px;
        width: 100%;
        border: none;
    }
    .stars {
        color: #E0B854;
        font-size: 0.9rem;
        white-space: nowrap;
    }
    .btn-success {
        background: var(--color-success);
        color: white;
        border: none;
        cursor: pointer;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 0.4rem;
    }
    .btn-delete {
        background: var(--color-danger);
        color: white;
        border: none;
        cursor: pointer;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 0.4rem;
    }
    .badge-success {
        background: rgba(16,185,129,0.1);
        color: #10B981;
        padding: 0.2rem 0.6rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .badge-warning {
        background: rgba(245,158,11,0.1);
        color: #F59E0B;
        padding: 0.2rem 0.6rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.delete-review-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const confirmMessage = button.getAttribute('data-confirm');
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush