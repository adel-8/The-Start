document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart JS loaded');

    // Helper: show toast
    function showToast(message, isError = false) {
        let toast = document.querySelector('.toast-notify');
        if (toast) toast.remove();

        toast = document.createElement('div');
        toast.className = 'toast-notify';
        toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    // Update cart count in navbar
    function updateCartCount() {
        fetch('/cart/count', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            const countElem = document.querySelector('.cart-count');
            if (countElem) countElem.textContent = data.count;
            const mobileCount = document.querySelector('.cart-count-mobile');
            if (mobileCount) mobileCount.textContent = data.count;
        })
        .catch(error => console.error('Error updating cart count:', error));
    }

    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';

    function formatCurrency(value) {
        return `${currencySymbol} ${parseFloat(value).toFixed(2)}`;
    }

    // Update totals (subtotal & total)
    function updateTotals() {
        const subtotalElem = document.getElementById('subtotal');
        const totalElem = document.getElementById('total');
        if (!subtotalElem || !totalElem) return;

        let subtotal = 0;
        const items = document.querySelectorAll('.cart-item');
        items.forEach(item => {
            const priceText = item.querySelector('.item-price')?.innerText;
            const price = priceText ? parseFloat(priceText.replace(/[^\d.-]/g, '')) : 0;
            const qty = parseInt(item.querySelector('.qty-input').value);
            const subtotalValue = price * qty;
            const subtotalSpan = item.querySelector('.subtotal-value');
            if (subtotalSpan) subtotalSpan.innerText = subtotalValue.toFixed(2);
            subtotal += subtotalValue;
        });
        subtotalElem.innerText = formatCurrency(subtotal);
        totalElem.innerText = formatCurrency(subtotal);
    }

    // Show empty cart message
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

    // Update quantity (waits for server)
    function updateQuantity(productId, newQuantity) {
        fetch('/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ product_id: productId, quantity: newQuantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (item) {
                    const input = item.querySelector('.qty-input');
                    input.value = newQuantity;
                    updateTotals();
                    updateCartCount();
                }
            } else {
                showToast('Failed to update quantity', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error', true);
        });
    }

    // Remove item
    function removeItem(productId) {
        fetch(`/cart/remove/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
                if (item) item.remove();
                updateTotals();
                showToast('Item removed');
                if (document.querySelectorAll('.cart-item').length === 0) {
                    showEmptyCart();
                }
                updateCartCount();
            } else {
                showToast('Failed to remove item', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error', true);
        });
    }

    // Clear cart
    function clearCart() {
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showEmptyCart();
                showToast('Cart cleared');
                updateCartCount();
            } else {
                showToast('Failed to clear cart', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error', true);
        });
    }

    // ---- Event handlers ----
    function handleDecrease(e) {
        const btn = e.currentTarget;
        const productId = btn.getAttribute('data-id');
        const input = document.querySelector(`.cart-item[data-id="${productId}"] .qty-input`);
        let current = parseInt(input.value);
        if (current > 1) {
            updateQuantity(productId, current - 1);
        }
    }

    function handleIncrease(e) {
        const btn = e.currentTarget;
        const productId = btn.getAttribute('data-id');
        const input = document.querySelector(`.cart-item[data-id="${productId}"] .qty-input`);
        let current = parseInt(input.value);
        updateQuantity(productId, current + 1);
    }

    function handleInputChange(e) {
        const input = e.currentTarget;
        const productId = input.getAttribute('data-id');
        let newVal = parseInt(input.value);
        if (isNaN(newVal) || newVal < 1) newVal = 1;
        input.value = newVal;
        updateQuantity(productId, newVal);
    }

    function handleRemove(e) {
        const btn = e.currentTarget;
        const productId = btn.getAttribute('data-id');
        removeItem(productId);
    }

    // Attach event listeners
    function attachEvents() {
        document.querySelectorAll('.qty-btn.decrease').forEach(btn => {
            btn.removeEventListener('click', handleDecrease);
            btn.addEventListener('click', handleDecrease);
        });
        document.querySelectorAll('.qty-btn.increase').forEach(btn => {
            btn.removeEventListener('click', handleIncrease);
            btn.addEventListener('click', handleIncrease);
        });
        document.querySelectorAll('.qty-input').forEach(input => {
            input.removeEventListener('change', handleInputChange);
            input.addEventListener('change', handleInputChange);
        });
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.removeEventListener('click', handleRemove);
            btn.addEventListener('click', handleRemove);
        });
    }

    // Clear cart button
    const clearBtn = document.getElementById('clearCartBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearCart();
        });
    }

    // Initialize
    const cartItems = document.querySelectorAll('.cart-item');
    if (cartItems.length === 0) {
        showEmptyCart();
    } else {
        attachEvents();
        updateTotals();
    }

    // Ensure navbar count is correct on page load
    updateCartCount();
});