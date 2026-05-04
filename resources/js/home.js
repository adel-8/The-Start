document.addEventListener('DOMContentLoaded', function() {
    // ----- Hero Slider -----
    let slideIndex = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.dot');

    function showSlide(n) {
        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (dots[i]) dots[i].classList.remove('active');
        });
        slides[n].classList.add('active');
        if (dots[n]) dots[n].classList.add('active');
        slideIndex = n;
    }

    function nextSlide() {
        let next = (slideIndex + 1) % slides.length;
        showSlide(next);
    }

    let interval = setInterval(nextSlide, 10000);

    if (dots.length) {
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                clearInterval(interval);
                showSlide(i);
                interval = setInterval(nextSlide, 10000);
            });
        });
    }

    const slider = document.querySelector('.hero-slider');
    if (slider) {
        slider.addEventListener('mouseenter', () => clearInterval(interval));
        slider.addEventListener('mouseleave', () => {
            interval = setInterval(nextSlide, 10000);
        });
    }

    // ----- Helper Functions (toast & cart count) -----
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
            const desktopCount = document.querySelector('.cart-count');
            if (desktopCount) desktopCount.textContent = data.count;
            const mobileCount = document.querySelector('.cart-count-mobile');
            if (mobileCount) mobileCount.textContent = data.count;
        })
        .catch(error => console.error('Error updating cart count:', error));
    }

    // ----- Add to Cart Buttons -----
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            const productName = this.getAttribute('data-name');
            const productPrice = this.getAttribute('data-price');

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
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`🛍️ ${productName} added to cart — $${productPrice}`);
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
    });

    // ----- More Details Buttons -----
    document.querySelectorAll('.details-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const slug = this.getAttribute('data-slug');
            if (slug) {
                window.location.href = `/product/${slug}`;
            }
        });
    });
});