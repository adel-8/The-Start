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
        fetch('/cart/count', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.count;
            });
        })
        .catch(error => console.error('Error updating cart count:', error));
    }

    function addToCartHandler(e) {
        e.preventDefault();
        const btn = e.currentTarget;
        const productId = btn.dataset.id;
        const productName = btn.dataset.name;
        const productPrice = btn.dataset.price;
        const colorId = btn.dataset.colorId || null;
        const quantity = 1; // default for product cards

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
                quantity: quantity,
                color_id: colorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`🛍️ ${productName} added to cart`);
                updateCartCount();
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
            } else {
                showToast(data.message || 'Failed to add item', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Network error, please try again', true);
        });
    }

    // Attach to all add-to-cart buttons except those on the product details page
    document.querySelectorAll('.add-cart-btn:not(.disabled)').forEach(btn => {
        if (btn.closest('.product-details')) return; // skip product details page
        btn.removeEventListener('click', addToCartHandler);
        btn.addEventListener('click', addToCartHandler);
    });

    updateCartCount();
    window.addEventListener('cart-updated', updateCartCount);
});