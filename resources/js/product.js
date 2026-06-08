document.addEventListener('DOMContentLoaded', function() {
    // Quantity selector
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');

    if (quantityInput && decreaseBtn && increaseBtn) {
        const min = parseInt(quantityInput.min) || 1;
        const max = parseInt(quantityInput.max) || 999;
        decreaseBtn.addEventListener('click', () => {
            let current = parseInt(quantityInput.value);
            if (current > min) quantityInput.value = current - 1;
        });
        increaseBtn.addEventListener('click', () => {
            let current = parseInt(quantityInput.value);
            if (current < max) quantityInput.value = current + 1;
        });
        quantityInput.addEventListener('change', () => {
            let val = parseInt(this.value);
            if (isNaN(val)) val = min;
            if (val < min) val = min;
            if (val > max) val = max;
            this.value = val;
        });
    }

    // Helpers
    function showToast(message, isError = false) {
        let toast = document.querySelector('.toast-notify');
        if (toast) toast.remove();
        toast = document.createElement('div');
        toast.className = 'toast-notify';
        toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    function updateCartCount() {
        fetch('/cart/count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const counters = document.querySelectorAll('.cart-count');
                counters.forEach(el => el.textContent = data.count);
            })
            .catch(console.error);
    }

    // Add to Cart button – reads active swatch LIVE
    const addCartBtn = document.querySelector('.add-cart-btn:not(.disabled)');
    if (addCartBtn) {
        // Remove any existing listeners (clean slate)
        const newBtn = addCartBtn.cloneNode(true);
        addCartBtn.parentNode.replaceChild(newBtn, addCartBtn);
        const finalBtn = newBtn;

        finalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.disabled) return;
            this.disabled = true;

            const activeSwatch = document.querySelector('.color-swatch.active');
            const selectedColorId = activeSwatch ? activeSwatch.dataset.colorId : null;
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

            const swatches = document.querySelectorAll('.color-swatch');
            if (swatches.length > 1 && !selectedColorId) {
                showToast('Please select a colour', true);
                this.disabled = false;
                return;
            }

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    color_id: selectedColorId || null
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`🛍️ ${productName} added to cart`);
                    updateCartCount();
                    document.dispatchEvent(new CustomEvent('cartUpdated'));
                } else {
                    showToast(data.message || 'Failed to add item', true);
                }
            })
            .catch(() => showToast('Network error, please try again', true))
            .finally(() => setTimeout(() => finalBtn.disabled = false, 500));
        });
    }

    // Buy Now button
    const buyNowBtn = document.getElementById('buyNowBtn');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const addBtn = document.querySelector('.add-cart-btn:not(.disabled)');
            if (addBtn) addBtn.click();
            setTimeout(() => window.location.href = '/checkout', 600);
        });
    }
});