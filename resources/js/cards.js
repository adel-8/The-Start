document.addEventListener('DOMContentLoaded', function() {
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
                document.querySelectorAll('.cart-count').forEach(el => el.textContent = data.count);
            })
            .catch(console.error);
    }

    document.querySelectorAll('.add-cart-btn:not(.disabled)').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.disabled) return;
            this.disabled = true;

            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const colorId = this.dataset.colorId || null;
            const quantity = 1; // product cards always add 1

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
                    color_id: colorId
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
            .finally(() => setTimeout(() => this.disabled = false, 500));
        });
    });
});