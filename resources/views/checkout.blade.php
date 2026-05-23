@extends('layouts.app')

@section('title', __('messages.checkout_page_title'))

@push('styles')
    @vite('resources/css/checkout.css')
    <style>
        /* ── Mobile: order-summary appears BEFORE billing ── */
        @media (max-width: 768px) {
            .checkout-layout {
                display: flex !important;
                flex-direction: column !important;
            }
            .order-summary {
                order: -1 !important; /* show first on mobile */
            }
            .billing-section {
                order: 1 !important;  /* show second on mobile */
            }
        }
    </style>
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

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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

                {{-- ═══════════════════════════════════════
                     BILLING SECTION
                ═══════════════════════════════════════ --}}
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
                                            <div class="dropdown-option"
                                                 data-value="{{ $addr->id }}"
                                                 data-address="{{ $addr->address_line1 }}"
                                                 data-city="{{ $addr->city }}"
                                                 data-region="{{ $addr->state }}"
                                                 data-postal="{{ $addr->postal_code }}">
                                                {{ $addr->address_line1 }}, {{ $addr->city }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="address_id" id="addressIdInput"
                                           value="{{ $defaultAddress ? $defaultAddress->id : '' }}">
                                </div>
                            </div>
                        @endif
                    @endauth

                    {{-- Full Name --}}
                    <div class="form-group">
                        <label for="fullName">{{ __('messages.full_name') }} <span class="required">*</span></label>
                        <input type="text" name="full_name" id="fullName"
                               value="{{ old('full_name', auth()->check() ? auth()->user()->name : '') }}"
                               required>
                        @error('full_name') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email — OPTIONAL --}}
                    <div class="form-group">
                        <label for="email">
                            {{ __('messages.email_address') }}
                            <span class="optional-label">({{ __('messages.optional') }})</span>
                        </label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}">
                        @error('email') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <label for="phone">{{ __('messages.phone_number') }} <span class="required">*</span></label>
                        <input type="tel" name="phone" id="phone"
                               value="{{ old('phone', auth()->check() ? (auth()->user()->phone ?? '') : '') }}"
                               required>
                        @error('phone') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    {{-- Address fields (hidden when saved address is selected) --}}
                    <div id="addressFieldsWrapper">
                        <div class="form-group">
                            <label for="address">{{ __('messages.street_address') }} <span class="required">*</span></label>
                            <input type="text" name="address" id="address"
                                   value="{{ old('address') }}">
                            @error('address') <span class="error-message">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">{{ __('messages.city') }} <span class="required">*</span></label>
                                <input type="text" name="city" id="city"
                                       value="{{ old('city') }}">
                                @error('city') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="region">{{ __('messages.region_state') }}</label>
                                <select name="region" id="regionSelect">
                                    <option value="">{{ __('messages.select_region') }}</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>
                                            {{ $region }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                            {{-- Postal Code — OPTIONAL --}}
                            <div class="form-group">
                                <label for="postal_code">
                                    {{ __('messages.postal_code') }}
                                    <span class="optional-label">({{ __('messages.optional') }})</span>
                                </label>
                                <input type="text" name="postal_code" id="postal_code"
                                       value="{{ old('postal_code') }}">
                                @error('postal_code') <span class="error-message">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Payment Methods --}}
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

                    {{-- Order Notes — FIX: was not submitting value properly --}}
                    <div class="form-group">
                        <label for="notes">
                            {{ __('messages.order_notes') }}
                            <span class="optional-label">({{ __('messages.optional') }})</span>
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="{{ __('messages.order_notes_placeholder') ?? '' }}">{{ old('notes') }}</textarea>
                        @error('notes') <span class="error-message">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="place-order-btn" id="placeOrderBtn">
                        <i class="fas fa-check-circle"></i>
                        <span class="btn-text">{{ __('messages.place_order') }}</span>
                        <span class="btn-spinner" style="display:none;">{{ __('messages.processing') }}</span>
                    </button>
                </div>

                {{-- ═══════════════════════════════════════
                     ORDER SUMMARY (shown first on mobile via CSS order)
                ═══════════════════════════════════════ --}}
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
                        <div class="summary-row" id="discountRow" style="display:none;">
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
                        <input type="text" id="couponCode" name="coupon_code"
                               placeholder="{{ __('messages.coupon_code_placeholder') }}"
                               value="{{ old('coupon_code') }}">
                        <button type="button" id="applyCouponBtn">{{ __('messages.apply_coupon') }}</button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div id="toastMsg" class="toast-message">
    <i class="fas fa-check-circle"></i>
    <span id="toastText"></span>
</div>

@endsection

@push('scripts')
    @vite('resources/js/checkout.js')
    <script>
    // ── Saved address dropdown logic ──
    document.addEventListener('DOMContentLoaded', function () {
        const trigger     = document.getElementById('addressDropdownTrigger');
        const options     = document.getElementById('addressDropdownOptions');
        const addressInput = document.getElementById('addressIdInput');
        const fieldsWrapper = document.getElementById('addressFieldsWrapper');

        if (!trigger) return;

        // Toggle dropdown
        trigger.addEventListener('click', function () {
            options.classList.toggle('open');
        });

        // Close when clicking outside
        document.addEventListener('click', function (e) {
            if (!trigger.contains(e.target) && !options.contains(e.target)) {
                options.classList.remove('open');
            }
        });

        // Handle option selection
        options.querySelectorAll('.dropdown-option').forEach(function (option) {
            option.addEventListener('click', function () {
                const value   = this.dataset.value;
                const label   = this.textContent.trim();
                const address = this.dataset.address || '';
                const city    = this.dataset.city    || '';
                const region  = this.dataset.region  || '';
                const postal  = this.dataset.postal  || '';

                // Update trigger label
                trigger.querySelector('.trigger-label').textContent = label;
                addressInput.value = value;
                options.classList.remove('open');

                if (value) {
                    // Saved address selected — hide manual fields, remove required
                    if (fieldsWrapper) {
                        fieldsWrapper.style.display = 'none';
                        fieldsWrapper.querySelectorAll('input, select').forEach(function (el) {
                            el.removeAttribute('required');
                        });
                    }
                } else {
                    // New address — show manual fields
                    if (fieldsWrapper) {
                        fieldsWrapper.style.display = 'block';
                        // Restore required on address and city
                        const addressEl = document.getElementById('address');
                        const cityEl    = document.getElementById('city');
                        if (addressEl) addressEl.setAttribute('required', 'required');
                        if (cityEl)    cityEl.setAttribute('required', 'required');
                    }
                    // Clear hidden inputs
                    addressInput.value = '';
                }

                // Pre-fill fields if saved address
                const addressEl = document.getElementById('address');
                const cityEl    = document.getElementById('city');
                const regionEl  = document.getElementById('regionSelect');
                const postalEl  = document.getElementById('postal_code');
                if (addressEl) addressEl.value = address;
                if (cityEl)    cityEl.value    = city;
                if (postalEl)  postalEl.value  = postal;
                if (regionEl && region) {
                    Array.from(regionEl.options).forEach(function (opt) {
                        opt.selected = opt.value === region;
                    });
                    // Trigger change so shipping cost updates
                    regionEl.dispatchEvent(new Event('change'));
                }
            });
        });

        // On load: if default address is selected, hide fields
        if (addressInput && addressInput.value) {
            if (fieldsWrapper) {
                fieldsWrapper.style.display = 'none';
                fieldsWrapper.querySelectorAll('input, select').forEach(function (el) {
                    el.removeAttribute('required');
                });
            }
        }
    });
    </script>
@endpush