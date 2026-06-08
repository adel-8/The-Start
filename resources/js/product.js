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

    // Helper: show toast (kept for buy‑now button, but global.js already provides it)
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

    // Update cart count in navbar (kept for buy‑now button)
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

            // Trigger the global add-to-cart handler by simulating a click on the add button
            const addBtn = document.querySelector('.add-cart-btn:not(.disabled)');
            if (addBtn) addBtn.click();

            setTimeout(() => window.location.href = '/checkout', 600);
        });
    }
});