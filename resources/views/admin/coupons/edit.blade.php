@extends('admin.layouts.app')

@section('title', __('admin.edit_coupon') . ': ' . $coupon->code)

@section('content')
<div class="edit-coupon-header">
    <h1>{{ __('admin.edit_coupon') }}: {{ $coupon->code }}</h1>
    <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_coupons') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="coupon-form">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="code">{{ __('admin.coupon_code') }} *</label>
        <input type="text" name="code" id="code" value="{{ old('code', $coupon->code) }}" required>
        @error('code') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="discount_type">{{ __('admin.discount_type') }} *</label>
            <select name="discount_type" id="discount_type" required>
                <option value="percentage" {{ old('discount_type', $coupon->discount_type) == 'percentage' ? 'selected' : '' }}>{{ __('admin.percentage') }}</option>
                <option value="fixed" {{ old('discount_type', $coupon->discount_type) == 'fixed' ? 'selected' : '' }}>{{ __('admin.fixed_amount') }}</option>
            </select>
        </div>
        <div class="form-group">
            <label for="discount_value">{{ __('admin.discount_value') }} *</label>
            <input type="number" step="0.01" name="discount_value" id="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" required>
            <small id="discount_value_suffix">
                @if(old('discount_type', $coupon->discount_type) == 'percentage')
                    %
                @else
                    {{ __('admin.currency_symbol') }}
                @endif
            </small>
            @error('discount_value') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="min_order_amount">{{ __('admin.min_order_amount') }}</label>
            <input type="number" step="0.01" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}">
            <small>{{ __('admin.leave_blank_unlimited') }}</small>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="valid_from">{{ __('admin.valid_from') }} *</label>
            <input type="datetime-local" name="valid_from" id="valid_from" value="{{ old('valid_from', optional($coupon->valid_from)->format('Y-m-d\TH:i')) }}" required>
            @error('valid_from') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
            <label for="valid_to">{{ __('admin.valid_to') }} *</label>
            <input type="datetime-local" name="valid_to" id="valid_to" value="{{ old('valid_to', optional($coupon->valid_to)->format('Y-m-d\TH:i')) }}" required>
            @error('valid_to') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="usage_limit_per_user">{{ __('admin.usage_limit_per_user') }}</label>
            <input type="number" name="usage_limit_per_user" id="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}">
            <small>{{ __('admin.leave_blank_unlimited') }}</small>
        </div>
        <div class="form-group">
            <label for="total_usage_limit">{{ __('admin.total_usage_limit') }}</label>
            <input type="number" name="total_usage_limit" id="total_usage_limit" value="{{ old('total_usage_limit', $coupon->total_usage_limit) }}">
            <small>{{ __('admin.leave_blank_unlimited') }}</small>
        </div>
    </div>

    <div class="form-group checkbox-group">
        <label>
            <input type="checkbox" name="active" value="1" {{ old('active', $coupon->active) ? 'checked' : '' }}>
            {{ __('admin.active') }}
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">{{ __('admin.update_coupon') }}</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .edit-coupon-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .coupon-form {
        background: var(--color-surface);
        padding: 1.5rem;
        border-radius: 1rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
    }
    .form-row {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .form-group {
        flex: 1;
        min-width: 200px;
        margin-bottom: 1rem;
        position: relative;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--color-text);
    }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        font-family: inherit;
        font-size: 0.9rem;
        transition: 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 2px rgba(100,95,125,0.1);
    }
    .error {
        color: var(--color-danger);
        font-size: 0.75rem;
        display: block;
        margin-top: 0.25rem;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        margin-top: 0.5rem;
    }
    .checkbox-group label {
        margin-bottom: 0;
        font-weight: normal;
    }
    .form-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    small {
        display: block;
        font-size: 0.7rem;
        color: var(--color-muted);
        margin-top: 0.25rem;
    }
    #discount_value_suffix {
        position: absolute;
        right: 10px;
        top: 38px;
        font-size: 0.8rem;
        color: var(--color-muted);
    }
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        .edit-coupon-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Update discount value suffix when discount type changes
    const discountTypeSelect = document.getElementById('discount_type');
    const discountValueSuffix = document.getElementById('discount_value_suffix');
    if (discountTypeSelect && discountValueSuffix) {
        discountTypeSelect.addEventListener('change', function() {
            if (this.value === 'percentage') {
                discountValueSuffix.textContent = '%';
            } else {
                discountValueSuffix.textContent = '{{ __("admin.currency_symbol") }}';
            }
        });
    }

    // Optional: auto-format datetime-local inputs to local timezone
    const validFrom = document.getElementById('valid_from');
    const validTo = document.getElementById('valid_to');
    if (validFrom && validFrom.value) {
        // Already in Y-m-d\TH:i format from server
    }
</script>
@endpush