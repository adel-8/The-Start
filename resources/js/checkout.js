document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // ADDRESS DROPDOWN & FIELD TOGGLING
    // ============================================
    const addressFieldsWrapper = document.getElementById('addressFieldsWrapper');
    const addressField = document.getElementById('address');
    const cityField = document.getElementById('city');
    const regionField = document.getElementById('region');
    const postalCodeField = document.getElementById('postal_code');
    const addressFields = [addressField, cityField, regionField, postalCodeField];

    function toggleAddressFields(useSaved) {
        if (useSaved) {
            addressFieldsWrapper.style.display = 'none';
            addressFields.forEach(field => {
                if (field) {
                    field.disabled = true;
                    field.removeAttribute('required');
                }
            });
        } else {
            addressFieldsWrapper.style.display = 'block';
            addressFields.forEach(field => {
                if (field) {
                    field.disabled = false;
                    field.setAttribute('required', 'required');
                }
            });
        }
    }

    function closeDropdown() {
        const optionsContainer = document.getElementById('addressDropdownOptions');
        optionsContainer?.classList.remove('open');
    }

    function openDropdown() {
        const optionsContainer = document.getElementById('addressDropdownOptions');
        optionsContainer?.classList.add('open');
    }

    function selectOption(value, label) {
        const trigger = document.getElementById('addressDropdownTrigger');
        const triggerLabel = trigger?.querySelector('.trigger-label');
        const addressIdInput = document.getElementById('addressIdInput');
        if (triggerLabel) triggerLabel.innerText = label;
        if (addressIdInput) addressIdInput.value = value;
        closeDropdown();
        toggleAddressFields(value !== '');
    }

    const dropdownWrapper = document.getElementById('addressDropdownWrapper');
    const trigger = document.getElementById('addressDropdownTrigger');
    const optionsContainer = document.getElementById('addressDropdownOptions');

    if (trigger && optionsContainer) {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            if (optionsContainer.classList.contains('open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        document.querySelectorAll('.dropdown-option').forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                const value = option.getAttribute('data-value');
                const label = option.innerText;
                selectOption(value, label);
            });
        });

        document.addEventListener('click', (e) => {
            if (dropdownWrapper && !dropdownWrapper.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    // Initial state based on preselected address (if any)
    const addressIdInput = document.getElementById('addressIdInput');
    if (addressIdInput && addressIdInput.value) {
        toggleAddressFields(true);
    } else {
        toggleAddressFields(false);
    }

    // ============================================
    // COUPON APPLICATION (AJAX)
    // ============================================
    const couponBtn = document.getElementById('applyCouponBtn');
    const couponInput = document.getElementById('couponCode');
    const toastMsg = document.getElementById('toastMsg');
    const toastText = document.getElementById('toastText');
    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';

    function formatCurrency(value) {
        return `${currencySymbol} ${parseFloat(value).toFixed(2)}`;
    }

    function showToast(message, isError = false) {
        if (!toastMsg) return;
        toastText.textContent = message;
        toastMsg.style.backgroundColor = isError ? '#dc3545' : 'var(--color-primary)';
        toastMsg.style.display = 'block';
        setTimeout(() => {
            toastMsg.style.display = 'none';
        }, 3000);
    }

    if (couponBtn) {
        couponBtn.addEventListener('click', function() {
            const code = couponInput.value.trim();
            if (!code) return;
            fetch('/coupon/apply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const discountElem = document.getElementById('discountAmount');
                    const discountRow = document.getElementById('discountRow');
                    const grandTotalElem = document.getElementById('grandTotal');
                    if (discountElem) discountElem.innerText = `-${formatCurrency(data.discount)}`;
                    if (discountRow) discountRow.style.display = 'flex';
                    if (grandTotalElem) grandTotalElem.innerText = formatCurrency(data.total);
                    showToast(`Coupon applied! You saved ${formatCurrency(data.discount)}`);
                } else {
                    showToast(data.message || 'Invalid coupon', true);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error applying coupon', true);
            });
        });
    }

    // ============================================
    // FORM SUBMISSION (COD vs Stripe)
    // ============================================
    const form = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('placeOrderBtn');
    const btnText = submitBtn?.querySelector('.btn-text');
    const btnSpinner = submitBtn?.querySelector('.btn-spinner');

    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

            // Stripe: normal form submit (no AJAX)
            if (selectedMethod === 'stripe') {
                e.preventDefault();
                form.action = form.dataset.stripeUrl;
                form.submit();
                return;
            }

            if (selectedMethod === 'baridimob') {
                // DO NOT override anything
                return; // normal submit → goes to checkout.store
            }

            // COD: AJAX submission (existing logic)
            e.preventDefault();

            if (submitBtn) {
                submitBtn.disabled = true;
                if (btnText) btnText.style.display = 'none';
                if (btnSpinner) btnSpinner.style.display = 'inline-block';
            }

            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    setTimeout(() => {
                        window.location.href = '/orders/' + data.order_number;
                    }, 1500);
                } else {
                    showToast(data.error || 'Order failed', true);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        if (btnText) btnText.style.display = 'inline-block';
                        if (btnSpinner) btnSpinner.style.display = 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error, please try again', true);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    if (btnText) btnText.style.display = 'inline-block';
                    if (btnSpinner) btnSpinner.style.display = 'none';
                }
            });
        });
    }
});