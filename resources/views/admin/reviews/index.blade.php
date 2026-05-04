@extends('admin.layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', __('admin.manage_reviews'))

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.product_reviews') }}</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="admin-table">
    <thead>
        
            <th>{{ __('admin.id') }}</th>
            <th>{{ __('admin.product') }}</th>
            <th>{{ __('admin.user') }}</th>
            <th>{{ __('admin.rating') }}</th>
            <th>{{ __('admin.comment') }}</th>
            <th>{{ __('admin.status') }}</th>
            <th>{{ __('admin.date') }}</th>
            <th>{{ __('admin.actions') }}</th>
        </thead>
    <tbody>
        @foreach($reviews as $review)
        
            <td>{{ $review->id }}</td>
            <td><a href="{{ route('product.show', $review->product->slug) }}" target="_blank">{{ $review->product->name }}</a></td>
            <td>{{ $review->user->name ?? $review->user->username }}</td>
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
            <td>
                @if(!$review->approved)
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-sm btn-success">{{ __('admin.approve') }}</button>
                    </form>
                @endif
                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-sm btn-delete delete-review-btn" data-confirm="{{ __('admin.delete_review_confirm') }}">{{ __('admin.delete') }}</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $reviews->links() }}
@endsection

@push('styles')
<style>
    .stars { color: #E0B854; font-size: 0.9rem; }
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