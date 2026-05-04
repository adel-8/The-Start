document.addEventListener('DOMContentLoaded', function() {
    // Quantity selector
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');

    if (quantityInput && decreaseBtn && increaseBtn) {
        const min = parseInt(quantityInput.min) || 1;
        const max = parseInt(quantityInput.max) || 999;

        decreaseBtn.addEventListener('click', function() {
            let current = parseInt(quantityInput.value);
            if (current > min) {
                quantityInput.value = current - 1;
            }
        });

        increaseBtn.addEventListener('click', function() {
            let current = parseInt(quantityInput.value);
            if (current < max) {
                quantityInput.value = current + 1;
            }
        });

        quantityInput.addEventListener('change', function() {
            let val = parseInt(this.value);
            if (isNaN(val)) val = min;
            if (val < min) val = min;
            if (val > max) val = max;
            this.value = val;
        });
    }

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

    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';
    function formatCurrency(value) {
        return `${currencySymbol} ${parseFloat(value).toFixed(2)}`;
    }

    // Update cart count in navbar
    function updateCartCount() {
        fetch('/cart/count', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            const desktopCount = document.querySelector('.cart-count');
            if (desktopCount) desktopCount.textContent = data.count;
            const mobileCount = document.querySelector('.cart-count-mobile');
            if (mobileCount) mobileCount.textContent = data.count;
        })
        .catch(error => console.error('Error updating cart count:', error));
    }

    // Add to cart button (AJAX)
    const addCartBtn = document.querySelector('.add-cart-btn:not(.disabled)');
    if (addCartBtn) {
        addCartBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const productPrice = this.getAttribute('data-price');
            const quantity = quantityInput ? quantityInput.value : 1;

            if (!productId) {
                showToast('Product ID missing', true);
                return;
            }

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`🛍️ ${productName} (x${quantity}) added to cart — $${(productPrice * quantity).toFixed(2)}`);
                    updateCartCount();
                } else {
                    showToast(data.message || 'Failed to add item', true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error, please try again', true);
            });
        });
    }

    // Buy Now button
    const buyNowBtn = document.getElementById('buyNowBtn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const productId = document.querySelector('.add-cart-btn')?.getAttribute('data-id');
            const quantity = quantityInput ? quantityInput.value : 1;

            if (!productId) {
                showToast('Product not found', true);
                return;
            }

            // First add to cart, then redirect to checkout
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/checkout';
                } else {
                    showToast(data.message || 'Failed to add item', true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network error, please try again', true);
            });
        });
    }
});

