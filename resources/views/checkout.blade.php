@extends('layouts.app')

@section('title', __('messages.checkout_page_title'))

@push('styles')
    @vite('resources/css/checkout.css')
@endpush

@section('content')
<div class="checkout-wrapper">
    <div class="container">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="checkoutForm"
            method="POST"
            action="{{ route('checkout.store') }}"
            data-stripe-url="{{ route('stripe.checkout') }}"
            data-checkout-url="{{ route('checkout.store') }}"
        >
            @csrf
            <div class="checkout-layout">
                <div class="billing-section">
                    <h2>{{ __('messages.billing_details') }}</h2>

                    @auth
                        @if($addresses->count())
                            <div class="address-selection">
                                <label>{{ __('messages.select_saved_address') }}</label>
                                <div class="address-dropdown-wrapper" id="addressDropdownWrapper">
                                    <div class="dropdown-trigger" id="addressDropdownTrigger">
                                        <span class="trigger-label">
                                            @if($defaultAddress)
                                                {{ $defaultAddress->address_line1 }}, {{ $defaultAddress->city }}
                                            @else
                                                {{ __('messages.new_address') }}
                                            @endif
                                        </span>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div class="dropdown-options" id="addressDropdownOptions">
                                        <div class="dropdown-option" data-value="">{{ __('messages.new_address') }}</div>
                                        @foreach($addresses as $addr)
                                            <div class="dropdown-option" data-value="{{ $addr->id }}" data-region="{{ $addr->state }}">
                                                {{ $addr->address_line1 }}, {{ $addr->city }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="address_id" id="addressIdInput" value="{{ $defaultAddress ? $defaultAddress->id : '' }}">
                                </div>
                            </div>
                        @endif
                    @endauth

                    {{-- Always visible fields --}}
                    <div class="form-group">
                        <label for="fullName">{{ __('messages.full_name') }}</label>
                        <input type="text" name="full_name" id="fullName" value="{{ old('full_name', auth()->check() ? auth()->user()->name : '') }}" required>
                        @error('full_name') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">{{ __('messages.email_address') }}</label>
                        <input type="email" name="email" id="email" value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}" required>
                        @error('email') <span class="error-message">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ __('messages.phone_number') }}</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', auth()->check() ? auth()->user()->phone ?? '' : '') }}" required>
                        @error('phone') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    {{-- Address fields (toggled based on saved address selection) --}}
                    <div id="addressFieldsWrapper">
                        <div class="form-group">
                            <label for="address">{{ __('messages.street_address') }}</label>
                            <input type="text" name="address" id="address" value="{{ old('address') }}" required>
                            @error('address') <span class="error-message">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">{{ __('messages.city') }}</label>
                                <input type="text" name="city" id="city" value="{{ old('city') }}" required>
                                @error('city') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="region">{{ __('messages.region_state') }}</label>
                                <select name="region" id="regionSelect" required>
                                    <option value="">{{ __('messages.select_region') }}</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>{{ $region }}</option>
                                    @endforeach
                                </select>
                                @error('region') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="postal_code">{{ __('messages.postal_code') }}</label>
                                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}">
                            </div>
                        </div>
                    </div>

                    <h2>{{ __('messages.payment_method') }}</h2>
                    <div class="payment-methods">
                        @if(empty($enabledPayments))
                            <div class="alert alert-warning">{{ __('messages.no_payment_methods_available') }}</div>
                        @else
                            @foreach($enabledPayments as $method)
                                @if($method === 'cash_on_delivery')
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="cash_on_delivery" {{ $loop->first ? 'checked' : '' }}>
                                        <strong><i class="fas fa-money-bill-wave"></i> {{ __('messages.cash_on_delivery') }}</strong>
                                        <p>{{ __('messages.cash_on_delivery_desc') }}</p>
                                    </label>
                                @elseif($method === 'baridimob')
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="baridimob" {{ $loop->first ? 'checked' : '' }}>
                                        <strong><i class="fas fa-money-bill"></i> {{ __('messages.baridimob') }}</strong>
                                        <p>{{ __('messages.baridimob_desc') }}</p>
                                    </label>
                                @elseif($method === 'stripe')
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="stripe" {{ $loop->first ? 'checked' : '' }}>
                                        <strong><i class="fab fa-stripe"></i> {{ __('messages.stripe') }}</strong>
                                        <p>{{ __('messages.stripe_desc') }}</p>
                                    </label>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="notes">{{ __('messages.order_notes') }}</label>
                        <textarea name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit" class="place-order-btn" id="placeOrderBtn">
                        <i class="fas fa-check-circle"></i> <span class="btn-text">{{ __('messages.place_order') }}</span>
                        <span class="btn-spinner" style="display: none;">{{ __('messages.processing') }}</span>
                    </button>
                </div>

                <div class="order-summary">
                    <h2>{{ __('messages.order_summary') }}</h2>
                    <div class="cart-items-list">
                        @php $subtotal = 0; @endphp
                        @foreach($cart as $id => $item)
                            @php $subtotal += $item['price'] * $item['quantity']; @endphp
                            <div class="cart-item" data-id="{{ $id }}">
                                <div class="item-image">
                                    @if($item['image'])
                                        <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}">
                                    @else
                                        <i class="fas fa-image"></i>
                                    @endif
                                </div>
                                <div class="item-details">
                                    <h4>{{ $item['name'] }}</h4>
                                    <div class="item-price">{{ format_currency($item['price']) }}</div>
                                    <div class="item-quantity">{{ __('messages.quantity_short') }}: {{ $item['quantity'] }}</div>
                                </div>
                                <div class="item-total">{{ format_currency($item['price'] * $item['quantity']) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>{{ __('messages.subtotal') }}</span>
                            <span id="subtotalAmount">{{ format_currency($subtotal) }}</span>
                        </div>
                        <div class="summary-row" id="discountRow" style="display: none;">
                            <span>{{ __('messages.discount') }}</span>
                            <span id="discountAmount">-{{ format_currency(0) }}</span>
                        </div>
                        <div class="summary-row" id="shippingRow">
                            <span>{{ __('messages.shipping') }}</span>
                            <span id="shippingCost">{{ format_currency(0) }}</span>
                        </div>
                        <div class="summary-row total">
                            <span><strong>{{ __('messages.total') }}</strong></span>
                            <span class="highlight" id="grandTotal">{{ format_currency($subtotal) }}</span>
                        </div>
                    </div>

                    <div class="coupon-section">
                        <input type="text" id="couponCode" name="coupon_code" placeholder="{{ __('messages.coupon_code_placeholder') }}">
                        <button type="button" id="applyCouponBtn">{{ __('messages.apply_coupon') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="toastMsg" class="toast-message"><i class="fas fa-check-circle"></i> <span id="toastText"></span></div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============================================
        // Shipping regions and costs (only region-based)
        // ============================================
        const regionCosts = @json($regionCosts);
        const regionSelect = document.getElementById('regionSelect');
        const shippingSpan = document.getElementById('shippingCost');
        const grandTotalSpan = document.getElementById('grandTotal');
        const subtotalSpan = document.getElementById('subtotalAmount');
        const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';
        let currentSubtotal = parseFloat(subtotalSpan.innerText.replace(/[^\d.-]/g, ''));

        function formatCurrency(value) {
            return `${currencySymbol} ${parseFloat(value).toFixed(2)}`;
        }

        function updateShippingAndTotal() {
            let selectedRegion = regionSelect.value;
            let shippingCost = 0;
            if (selectedRegion && regionCosts[selectedRegion] !== undefined) {
                shippingCost = parseFloat(regionCosts[selectedRegion]);
            }
            shippingSpan.innerText = formatCurrency(shippingCost);
            let total = currentSubtotal + shippingCost;
            let discountRow = document.getElementById('discountRow');
            let discountAmountElem = document.getElementById('discountAmount');
            if (discountRow.style.display !== 'none') {
                let discount = parseFloat(discountAmountElem.innerText.replace(/[^\d.-]/g, ''));
                total -= discount;
            }
            grandTotalSpan.innerText = formatCurrency(total);
        }

        if (regionSelect) {
            regionSelect.addEventListener('change', updateShippingAndTotal);
            // initial update if region already selected
            if (regionSelect.value) {
                updateShippingAndTotal();
            }
        }

        // ============================================
        // Address dropdown and field visibility (existing)
        // ============================================
        const addressWrapper = document.getElementById('addressDropdownWrapper');
        const trigger = document.getElementById('addressDropdownTrigger');
        const dropdownOptions = document.getElementById('addressDropdownOptions');
        const addressIdInput = document.getElementById('addressIdInput');
        const addressFieldsWrapper = document.getElementById('addressFieldsWrapper');

        if (addressWrapper && trigger && dropdownOptions && addressIdInput) {
            // Toggle dropdown
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownOptions.classList.toggle('show');
            });
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!addressWrapper.contains(e.target)) {
                    dropdownOptions.classList.remove('show');
                }
            });
            // Select option
            const options = dropdownOptions.querySelectorAll('.dropdown-option');
            options.forEach(opt => {
                opt.addEventListener('click', function(e) {
                    const value = this.getAttribute('data-value');
                    const region = this.getAttribute('data-region');
                    addressIdInput.value = value || '';
                    trigger.querySelector('.trigger-label').innerText = this.innerText;

                    // Toggle address fields visibility
                    if (value === '') {
                        // New address: show fields and clear values
                        addressFieldsWrapper.style.display = 'block';
                        document.getElementById('address').value = '';
                        document.getElementById('city').value = '';
                        if (regionSelect) regionSelect.value = '';
                        document.getElementById('postal_code').value = '';
                    } else {
                        // Saved address: hide fields, optionally prefill region
                        addressFieldsWrapper.style.display = 'none';
                        if (region && regionSelect) {
                            regionSelect.value = region;
                            updateShippingAndTotal();
                        }
                    }
                    dropdownOptions.classList.remove('show');
                });
            });
        }

        // ============================================
        // Coupon logic (existing, keep as is)
        // ============================================
        const applyCouponBtn = document.getElementById('applyCouponBtn');
        const couponCodeInput = document.getElementById('couponCode');
        if (applyCouponBtn) {
            applyCouponBtn.addEventListener('click', function() {
                const code = couponCodeInput.value.trim();
                if (!code) {
                    showToast('{{ __("messages.enter_coupon_code") }}', 'error');
                    return;
                }
                fetch('{{ route("coupon.apply") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code: code })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const discountRow = document.getElementById('discountRow');
                        const discountAmount = document.getElementById('discountAmount');
                        discountRow.style.display = 'flex';
                        discountAmount.innerText = '- ' + formatCurrency(data.discount);
                        // Update total
                        let shipping = parseFloat(shippingSpan.innerText.replace(/[^\d.-]/g, ''));
                        let total = currentSubtotal - parseFloat(data.discount) + (isNaN(shipping) ? 0 : shipping);
                        grandTotalSpan.innerText = formatCurrency(total);
                        showToast('{{ __("messages.coupon_applied") }}', 'success');
                    } else {
                        showToast(data.message || '{{ __("messages.invalid_coupon") }}', 'error');
                    }
                })
                .catch(error => {
                    showToast('{{ __("messages.error_applying_coupon") }}', 'error');
                });
            });
        }

        // Toast message helper
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toastMsg');
            const toastText = document.getElementById('toastText');
            toastText.innerText = message;
            toast.classList.add('show', type === 'success' ? 'toast-success' : 'toast-error');
            setTimeout(() => {
                toast.classList.remove('show', 'toast-success', 'toast-error');
            }, 3000);
        }

        // ============================================
        // Place order - ensure region is selected
        // ============================================
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        if (placeOrderBtn) {
            placeOrderBtn.addEventListener('click', function(e) {
                if (regionSelect && !regionSelect.value) {
                    e.preventDefault();
                    showToast('{{ __("messages.select_region_error") }}', 'error');
                    regionSelect.focus();
                }
                // Spinner handling (optional, keep existing)
            });
        }
    });
</script>
@endpush