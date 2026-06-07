document.addEventListener('DOMContentLoaded', function () {
    console.log('Cart JS loaded');

    // ── Toast ─────────────────────────────────────────────
    function showToast(message, isError = false) {
        let toast = document.querySelector('.toast-notify');
        if (toast) toast.remove();
        toast = document.createElement('div');
        toast.className = 'toast-notify';
        toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    // ── Navbar cart count ─────────────────────────────────
    function updateCartCount() {
        fetch('/cart/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                document.querySelectorAll('.cart-count, .cart-count-mobile')
                    .forEach(el => el.textContent = data.count);
            })
            .catch(console.error);
    }

    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';
    function formatCurrency(value) {
        return `${currencySymbol} ${parseFloat(value).toFixed(2)}`;
    }

    // ── Totals ────────────────────────────────────────────
    function updateTotals() {
        const subtotalElem = document.getElementById('subtotal');
        const totalElem    = document.getElementById('total');
        if (!subtotalElem || !totalElem) return;

        let subtotal = 0;
        document.querySelectorAll('.cart-item:not(.oos)').forEach(item => {
            const priceText = item.querySelector('.item-price')?.innerText || '0';
            const price     = parseFloat(priceText.replace(/[^\d.-]/g, '')) || 0;
            const input     = item.querySelector('.qty-input');
            const qty       = input ? parseInt(input.value) || 1 : 1;
            const span      = item.querySelector('.subtotal-value');
            if (span) span.innerText = formatCurrency(price * qty);
            subtotal += price * qty;
        });

        subtotalElem.innerText = formatCurrency(subtotal);
        totalElem.innerText    = formatCurrency(subtotal);
    }

    // ── Checkout button sync ──────────────────────────────
    // Called every time an item is removed or the cart changes.
    // If zero .oos items remain → unlock the checkout button.
    function syncCheckoutButton() {
        const checkoutBtn = document.querySelector('.btn-checkout');
        if (!checkoutBtn) return;

        const hasOos = document.querySelectorAll('.cart-item.oos').length > 0;

        if (hasOos) {
            checkoutBtn.classList.add('disabled-checkout');
            checkoutBtn.setAttribute('aria-disabled', 'true');
        } else {
            checkoutBtn.classList.remove('disabled-checkout');
            checkoutBtn.removeAttribute('aria-disabled');
        }

        // Also hide/show the OOS warning box inside the summary
        const oosBox = document.querySelector('.cart-summary .stock-error-box');
        if (oosBox) oosBox.style.display = hasOos ? '' : 'none';
    }

    // ── Empty cart ────────────────────────────────────────
    function showEmptyCart() {
        const cartLayout = document.querySelector('.cart-layout');
        if (cartLayout) cartLayout.remove();
        const container = document.querySelector('.cart-page .container');
        if (container && !document.querySelector('.empty-cart')) {
            container.insertAdjacentHTML('beforeend', `
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty.</p>
                    <a href="/Shop" class="btn-primary">Continue Shopping</a>
                </div>
            `);
        }
    }

    // ── Update quantity ───────────────────────────────────
    function updateQuantity(cartKey, newQuantity) {
        fetch('/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ cart_key: cartKey, quantity: newQuantity }),
        })
        .then(r => r.json())
        .then(data => {
            const item  = document.querySelector(`.cart-item[data-id="${cartKey}"]`);
            const input = item?.querySelector('.qty-input');

            if (data.success) {
                if (input) input.value = newQuantity;
                updateTotals();
                updateCartCount();
            } else {
                // Server capped the quantity (stock changed)
                if (data.capped_qty && input) {
                    input.value = data.capped_qty;
                    updateTotals();
                }
                showToast(data.message || 'Failed to update quantity', true);
            }
        })
        .catch(() => showToast('Network error', true));
    }

    // ── Remove item ───────────────────────────────────────
    function removeItem(productId) {
        fetch(`/cart/remove/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (item) item.remove();

                updateTotals();
                updateCartCount();
                showToast('Item removed');

                if (document.querySelectorAll('.cart-item').length === 0) {
                    showEmptyCart();
                } else {
                    // ← THE FIX: re-evaluate checkout button after removal
                    syncCheckoutButton();
                }
            } else {
                showToast('Failed to remove item', true);
            }
        })
        .catch(() => showToast('Network error', true));
    }

    // ── Clear cart ────────────────────────────────────────
    function clearCart() {
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showEmptyCart();
                showToast('Cart cleared');
                updateCartCount();
            } else {
                showToast('Failed to clear cart', true);
            }
        })
        .catch(() => showToast('Network error', true));
    }

    // ── Event handlers ────────────────────────────────────
    function handleDecrease(e) {
        const cartKey = e.currentTarget.dataset.id;
        const input   = document.querySelector(`.cart-item[data-id="${cartKey}"] .qty-input`);
        const current = parseInt(input.value);
        if (current > 1) {
            input.dataset.old = current;
            updateQuantity(cartKey, current - 1);
        }
    }

    function handleIncrease(e) {
        const btn     = e.currentTarget;
        const cartKey = btn.dataset.id;
        const max     = parseInt(btn.dataset.max) || 9999;
        const input   = document.querySelector(`.cart-item[data-id="${cartKey}"] .qty-input`);
        const current = parseInt(input.value);
        if (current >= max) {
            showToast(`Only ${max} available`, true);
            return;
        }
        input.dataset.old = current;
        updateQuantity(cartKey, current + 1);
    }

    function handleInputChange(e) {
        const input   = e.currentTarget;
        const cartKey = input.dataset.id;
        const max     = parseInt(input.max) || 9999;
        let val       = parseInt(input.value);
        if (isNaN(val) || val < 1) val = 1;
        if (val > max) val = max;
        input.value       = val;
        input.dataset.old = val;
        updateQuantity(cartKey, val);
    }

    function handleRemove(e) {
        removeItem(e.currentTarget.dataset.id);
    }

    // ── Attach events ─────────────────────────────────────
    function attachEvents() {
        document.querySelectorAll('.qty-btn.decrease').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true)); // remove old listeners
        });
        document.querySelectorAll('.qty-btn.increase').forEach(btn => {
            btn.replaceWith(btn.cloneNode(true));
        });
        document.querySelectorAll('.qty-btn.decrease').forEach(btn =>
            btn.addEventListener('click', handleDecrease));
        document.querySelectorAll('.qty-btn.increase').forEach(btn =>
            btn.addEventListener('click', handleIncrease));
        document.querySelectorAll('.qty-input').forEach(input =>
            input.addEventListener('change', handleInputChange));
        document.querySelectorAll('.remove-item').forEach(btn =>
            btn.addEventListener('click', handleRemove));
    }

    // ── Clear button ──────────────────────────────────────
    document.getElementById('clearCartBtn')?.addEventListener('click', e => {
        e.preventDefault();
        clearCart();
    });

    // ── Init ──────────────────────────────────────────────
    if (document.querySelectorAll('.cart-item').length === 0) {
        showEmptyCart();
    } else {
        attachEvents();
        updateTotals();
        syncCheckoutButton(); // set correct state on first load too
    }

    updateCartCount();
});