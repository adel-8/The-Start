document.addEventListener('DOMContentLoaded', function () {

    // ════════════════════════════════════════════════════
    // 1. SAVED ADDRESS DROPDOWN
    // ════════════════════════════════════════════════════
    const dropdownWrapper   = document.getElementById('addressDropdownWrapper');
    const trigger           = document.getElementById('addressDropdownTrigger');
    const optionsContainer  = document.getElementById('addressDropdownOptions');
    const addressIdInput    = document.getElementById('addressIdInput');
    const fieldsWrapper     = document.getElementById('addressFieldsWrapper');

    // Fields inside the address wrapper
    const addressField      = document.getElementById('address');
    const cityField         = document.getElementById('city');
    const regionField       = document.getElementById('regionSelect');

    function openDropdown() {
        if (!optionsContainer) return;
        optionsContainer.classList.add('open');
        if (trigger) {
            const icon = trigger.querySelector('i');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }

    function closeDropdown() {
        if (!optionsContainer) return;
        optionsContainer.classList.remove('open');
        if (trigger) {
            const icon = trigger.querySelector('i');
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }

    function showAddressFields() {
        if (!fieldsWrapper) return;
        fieldsWrapper.style.display = 'block';
        // Restore required
        if (addressField) { addressField.disabled = false; addressField.setAttribute('required', 'required'); }
        if (cityField)    { cityField.disabled = false;    cityField.setAttribute('required', 'required'); }
        if (regionField)  { regionField.disabled = false; }
    }

    function hideAddressFields() {
        if (!fieldsWrapper) return;
        fieldsWrapper.style.display = 'none';
        // Disable + remove required so form submits without them
        if (addressField) { addressField.disabled = true; addressField.removeAttribute('required'); }
        if (cityField)    { cityField.disabled = true;    cityField.removeAttribute('required'); }
        if (regionField)  { regionField.disabled = true; }
    }

    function prefillAddressFields(option) {
        const addr   = option.dataset.address || '';
        const city   = option.dataset.city    || '';
        const region = option.dataset.region  || '';
        const postal = option.dataset.postal  || '';

        if (addressField) addressField.value = addr;
        if (cityField)    cityField.value    = city;
        if (regionField && region) {
            Array.from(regionField.options).forEach(opt => {
                opt.selected = opt.value === region;
            });
            regionField.dispatchEvent(new Event('change'));
        }
        const postalField = document.getElementById('postal_code');
        if (postalField)  postalField.value  = postal;
    }

    if (trigger && optionsContainer) {
        // Toggle on trigger click
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            optionsContainer.classList.contains('open') ? closeDropdown() : openDropdown();
        });

        // Handle option selection
        optionsContainer.querySelectorAll('.dropdown-option').forEach(function (option) {
            option.addEventListener('click', function (e) {
                e.stopPropagation();
                const value = this.dataset.value || '';
                const label = this.textContent.trim();

                // Update trigger label
                const triggerLabel = trigger.querySelector('.trigger-label');
                if (triggerLabel) triggerLabel.textContent = label;

                // Update hidden input
                if (addressIdInput) addressIdInput.value = value;

                closeDropdown();

                if (value) {
                    // Saved address selected
                    prefillAddressFields(this);
                    hideAddressFields();
                } else {
                    // New address
                    showAddressFields();
                    if (addressIdInput) addressIdInput.value = '';
                }
            });
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (dropdownWrapper && !dropdownWrapper.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    // Initial state
    if (addressIdInput && addressIdInput.value) {
        hideAddressFields();
    } else {
        showAddressFields();
    }

    // ════════════════════════════════════════════════════
    // 2. DELIVERY TYPE VISUAL TOGGLE
    // ════════════════════════════════════════════════════
    document.querySelectorAll('.delivery-option').forEach(function (label) {
        label.addEventListener('click', function () {
            // Remove active from all
            document.querySelectorAll('.delivery-option').forEach(l => l.classList.remove('active'));
            // Add to clicked
            this.classList.add('active');
        });

        // Handle if already checked on load
        const radio = label.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            label.classList.add('active');
        }
    });

    // ════════════════════════════════════════════════════
    // 3. COUPON APPLICATION (AJAX)
    // ════════════════════════════════════════════════════
    const couponBtn     = document.getElementById('applyCouponBtn');
    const couponInput   = document.getElementById('couponCode');
    const toastMsg      = document.getElementById('toastMsg');
    const toastText     = document.getElementById('toastText');
    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';

    function formatCurrency(value) {
        return `${currencySymbol} ${parseFloat(value).toFixed(2)}`;
    }

    function showToast(message, isError = false) {
        if (!toastMsg) return;
        toastText.textContent = message;
        toastMsg.style.backgroundColor = isError ? '#dc3545' : 'var(--color-primary)';
        toastMsg.style.display = 'block';
        setTimeout(() => { toastMsg.style.display = 'none'; }, 3500);
    }

    if (couponBtn) {
        couponBtn.addEventListener('click', function () {
            const code = couponInput ? couponInput.value.trim() : '';
            if (!code) return;

            couponBtn.disabled = true;
            couponBtn.textContent = '...';

            fetch('/coupon/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code })
            })
            .then(res => res.json())
            .then(data => {
                couponBtn.disabled = false;
                couponBtn.textContent = document.documentElement.lang === 'ar' ? 'تطبيق' : 'Apply';

                if (data.success) {
                    const discountElem = document.getElementById('discountAmount');
                    const discountRow  = document.getElementById('discountRow');
                    const grandTotal   = document.getElementById('grandTotal');
                    if (discountElem) discountElem.textContent = `-${formatCurrency(data.discount)}`;
                    if (discountRow)  discountRow.style.display = 'flex';
                    if (grandTotal)   grandTotal.textContent    = formatCurrency(data.total);
                    showToast(`✓ Coupon applied! You saved ${formatCurrency(data.discount)}`);
                } else {
                    showToast(data.message || 'Invalid coupon', true);
                }
            })
            .catch(() => {
                couponBtn.disabled = false;
                couponBtn.textContent = 'Apply';
                showToast('Network error, please try again', true);
            });
        });
    }

    // ════════════════════════════════════════════════════
    // 4. SHIPPING COST UPDATE ON REGION CHANGE
    // ════════════════════════════════════════════════════
    const regionSelect   = document.getElementById('regionSelect');
    const shippingCostEl = document.getElementById('shippingCost');
    const grandTotalEl   = document.getElementById('grandTotal');
    const subtotalEl     = document.getElementById('subtotalAmount');

    // regionCosts passed from blade via data attribute or inline script
    // We read it from a hidden input added by the blade
    const regionCostsData = document.getElementById('regionCostsData');
    let regionCosts = {};
    if (regionCostsData) {
        try { regionCosts = JSON.parse(regionCostsData.value); } catch(e) {}
    }

    function updateShipping() {
        if (!regionSelect || !shippingCostEl || !grandTotalEl || !subtotalEl) return;
        const region = regionSelect.value;
        const shipping = regionCosts[region] ? parseFloat(regionCosts[region]) : 0;

        // Parse subtotal from current text
        const subtotalText = subtotalEl.textContent.replace(/[^\d.]/g, '');
        const subtotal = parseFloat(subtotalText) || 0;

        // Check for active discount
        const discountEl = document.getElementById('discountAmount');
        let discount = 0;
        if (discountEl && discountEl.textContent) {
            discount = parseFloat(discountEl.textContent.replace(/[^\d.]/g, '')) || 0;
        }

        const total = Math.max(0, subtotal - discount + shipping);

        if (shippingCostEl) shippingCostEl.textContent = shipping > 0 ? formatCurrency(shipping) : formatCurrency(0);
        if (grandTotalEl)   grandTotalEl.textContent   = formatCurrency(total);
    }

    if (regionSelect) {
        regionSelect.addEventListener('change', updateShipping);
    }

    // ════════════════════════════════════════════════════
    // 5. FORM SUBMISSION
    // ════════════════════════════════════════════════════
    const form      = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('placeOrderBtn');
    const btnText   = submitBtn?.querySelector('.btn-text');
    const btnSpinner = submitBtn?.querySelector('.btn-spinner');

    function setLoading(loading) {
        if (!submitBtn) return;
        submitBtn.disabled = loading;
        if (btnText)    btnText.style.display    = loading ? 'none'         : 'inline';
        if (btnSpinner) btnSpinner.style.display = loading ? 'inline-block' : 'none';
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

            // Stripe: redirect form to stripe URL
            if (selectedMethod === 'stripe') {
                e.preventDefault();
                form.action = form.dataset.stripeUrl;
                form.submit();
                return;
            }

            // BaridiMob: normal POST submit (no AJAX)
            if (selectedMethod === 'baridimob') {
                // Let it submit normally
                return;
            }

            // COD: AJAX submission
            e.preventDefault();
            setLoading(true);

            const formData = new FormData(form);

            fetch(form.dataset.checkoutUrl || form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Order placed!');
                    // FIX: redirect to checkout success page
                    setTimeout(() => {
                        window.location.href = data.redirect || '/';
                    }, 1000);
                } else {
                    setLoading(false);
                    // Show validation errors if returned
                    if (data.errors) {
                        const msgs = Object.values(data.errors).flat().join('\n');
                        showToast(msgs, true);
                    } else {
                        showToast(data.error || data.message || 'Order failed. Please try again.', true);
                    }
                }
            })
            .catch(err => {
                console.error('Checkout error:', err);
                setLoading(false);
                showToast('Network error. Please try again.', true);
            });
        });
    }

});