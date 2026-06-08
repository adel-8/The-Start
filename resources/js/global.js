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
                document.querySelectorAll('.cart-count').forEach(el => {
                    el.textContent = data.count;
                });
            })
            .catch(console.error);
    }

    // Single handler for all add-to-cart buttons (product cards + product detail page)
    function addToCartHandler(e) {
        e.preventDefault();
        const btn = e.currentTarget;

        // Prevent rapid double‑clicks
        if (btn.disabled) return;
        btn.disabled = true;

        const productId = btn.dataset.id;
        const productName = btn.dataset.name;
        const colorId = btn.dataset.colorId || null;

        // Get quantity: if there's a #quantity input on the page (product detail), use its value; otherwise default to 1
        const qtyInput = document.getElementById('quantity');
        let quantity = qtyInput ? parseInt(qtyInput.value) : 1;
        if (isNaN(quantity) || quantity < 1) quantity = 1;

        if (!productId) {
            showToast('Product ID missing', true);
            btn.disabled = false;
            return;
        }

        // For products with multiple color swatches, enforce color selection
        const swatches = document.querySelectorAll('.color-swatch');
        if (swatches.length > 1 && !colorId) {
            showToast('Please select a colour', true);
            btn.disabled = false;
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
                color_id: colorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`🛍️ ${productName} added to cart`);
                updateCartCount();
                // Trigger cart badge bounce (optional, handled elsewhere)
                document.dispatchEvent(new CustomEvent('cartUpdated'));
            } else {
                showToast(data.message || 'Failed to add item', true);
            }
        })
        .catch(() => showToast('Network error, please try again', true))
        .finally(() => {
            setTimeout(() => { btn.disabled = false; }, 500);
        });
    }

    // Attach the handler to ALL add-to-cart buttons (no skip)
    document.querySelectorAll('.add-cart-btn:not(.disabled)').forEach(btn => {
        // Remove any previous listener to avoid duplicates
        btn.removeEventListener('click', addToCartHandler);
        btn.addEventListener('click', addToCartHandler);
    });

    updateCartCount();
});