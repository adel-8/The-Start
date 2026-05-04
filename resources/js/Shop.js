// ============================================
// SHOP PAGE – FULL AJAX FILTERS, SORT & PAGINATION
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // ----- DOM Elements -----
    const filterForm = document.getElementById('filter-form');
    const productsArea = document.getElementById('products-area');
    const sortWrapper = document.getElementById('sortDropdown');
    const sortTrigger = document.getElementById('sortTrigger');
    const sortOptions = document.getElementById('sortOptions');
    const sortInput = document.getElementById('sortInput');
    const filterToggle = document.getElementById('filterToggleBtn');
    const filtersSidebar = document.getElementById('filtersSidebar');

    // Store the current URL (the one that loaded the page)
    let currentUrl = window.location.href;

    // Helper: Show Toast
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

    // Helper: Update Cart Count
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

    // AJAX: Fetch Products
    function fetchProducts(url, pushState = true) {
        if (productsArea) productsArea.classList.add('loading');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (productsArea) {
                productsArea.innerHTML = data.html + data.pagination;
                currentUrl = url;
                if (pushState) {
                    window.history.pushState({ url: url }, '', url);
                }
            }
        })
        .catch(error => console.error('Error fetching products:', error))
        .finally(() => {
            if (productsArea) productsArea.classList.remove('loading');
        });
    }

    // Handle back/forward navigation
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.url) {
            fetchProducts(event.state.url, false);
        } else {
            // If no state (initial page), reload normally (or use the initial URL)
            // But we already have the content, so we could do nothing.
        }
    });

    // Filter Form Submission (AJAX)
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = new URL(filterForm.action);
            const params = new URLSearchParams(new FormData(filterForm));
            url.search = params.toString();
            fetchProducts(url, true);
        });
    }

    // Pagination (delegation)
    if (productsArea) {
        productsArea.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                fetchProducts(paginationLink.href, true);
            }
        });
    }

    // CUSTOM SORT DROPDOWN (FIXED)
    if (sortWrapper && sortTrigger && sortOptions && sortInput) {
        const triggerLabel = sortTrigger.querySelector('.trigger-label');
        const options = sortOptions.querySelectorAll('.sort-option');

        // Set initial label and active state based on current URL parameters
        function setInitialSort() {
            const currentValue = sortInput.value;
            options.forEach(option => {
                if (option.dataset.value === currentValue) {
                    option.classList.add('active');
                    if (triggerLabel) {
                        triggerLabel.innerText = option.dataset.display || option.innerText;
                    }
                }
            });
        }
        setInitialSort();

        // Toggle dropdown
        sortTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            sortOptions.classList.toggle('active');
        });

        // Handle option click
        options.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();

                const value = this.dataset.value;
                const display = this.dataset.display || this.innerText;

                // Update hidden input
                sortInput.value = value;

                // Update label
                if (triggerLabel) triggerLabel.innerText = display;

                // Active class
                options.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');

                // Close dropdown
                sortOptions.classList.remove('active');

                // Submit via AJAX
                const url = new URL(filterForm.action);
                const params = new URLSearchParams(new FormData(filterForm));
                url.search = params.toString();
                fetchProducts(url, true);
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!sortWrapper.contains(e.target)) {
                sortOptions.classList.remove('active');
            }
        });
    }

    // Mobile Filter Toggle
    if (filterToggle && filtersSidebar) {
        filterToggle.addEventListener('click', function() {
            filtersSidebar.classList.toggle('open');
        });
    }

    // Add to Cart (AJAX, delegated)
    if (productsArea) {
        productsArea.addEventListener('click', function(e) {
            const button = e.target.closest('.add-cart-btn');
            if (button) {
                e.preventDefault();

                const productId = button.getAttribute('data-id');
                const productName = button.getAttribute('data-name');
                const productPrice = button.getAttribute('data-price');

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
                        showToast(`🛍️ ${productName} added to cart — ${formatCurrency(productPrice)}`);
                        updateCartCount();
                    } else {
                        showToast(data.message || 'Failed to add item', true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Network error, please try again', true);
                });
            }
        });
    }

    // More Details Button (redirect)
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.details-btn');
        if (button) {
            const slug = button.getAttribute('data-slug');
            if (slug) {
                window.location.href = `/product/${slug}`;
            }
        }
    });
});