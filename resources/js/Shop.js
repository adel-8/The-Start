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

    let currentUrl = window.location.href;

    // Helper: Show Toast (used for errors only)
    function showToast(message, isError = true) {
        let toast = document.querySelector('.toast-notify');
        if (toast) toast.remove();
        toast = document.createElement('div');
        toast.className = 'toast-notify';
        toast.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    // AJAX: Fetch Products
    function fetchProducts(url, pushState = true) {
        if (productsArea) productsArea.classList.add('loading');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(data => {
                if (productsArea) {
                    productsArea.innerHTML = data.html + data.pagination;
                    currentUrl = url;
                    if (pushState) window.history.pushState({ url: url }, '', url);
                }
            })
            .catch(error => console.error('Error fetching products:', error))
            .finally(() => {
                if (productsArea) productsArea.classList.remove('loading');
            });
    }

    // Handle back/forward navigation
    window.addEventListener('popstate', function(event) {
        if (event.state && event.state.url) fetchProducts(event.state.url, false);
    });

    // Filter Form Submission
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = new URL(filterForm.action);
            const params = new URLSearchParams(new FormData(filterForm));
            url.search = params.toString();
            fetchProducts(url, true);
        });
    }

    // Pagination delegation
    if (productsArea) {
        productsArea.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('.pagination a');
            if (paginationLink) {
                e.preventDefault();
                fetchProducts(paginationLink.href, true);
            }
        });
    }

    // Custom sort dropdown
    if (sortWrapper && sortTrigger && sortOptions && sortInput) {
        const triggerLabel = sortTrigger.querySelector('.trigger-label');
        const options = sortOptions.querySelectorAll('.sort-option');

        function setInitialSort() {
            const currentValue = sortInput.value;
            options.forEach(option => {
                if (option.dataset.value === currentValue) {
                    option.classList.add('active');
                    if (triggerLabel) triggerLabel.innerText = option.dataset.display || option.innerText;
                }
            });
        }
        setInitialSort();

        sortTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            sortOptions.classList.toggle('active');
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                sortInput.value = option.dataset.value;
                if (triggerLabel) triggerLabel.innerText = option.dataset.display || option.innerText;
                options.forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                sortOptions.classList.remove('active');
                const url = new URL(filterForm.action);
                const params = new URLSearchParams(new FormData(filterForm));
                url.search = params.toString();
                fetchProducts(url, true);
            });
        });

        document.addEventListener('click', (e) => {
            if (!sortWrapper.contains(e.target)) sortOptions.classList.remove('active');
        });
    }

    // Mobile filter toggle
    if (filterToggle && filtersSidebar) {
        filterToggle.addEventListener('click', () => filtersSidebar.classList.toggle('open'));
    }

    // More Details Buttons (redirect)
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.details-btn');
        if (button) {
            const slug = button.getAttribute('data-slug');
            if (slug) window.location.href = `/product/${slug}`;
        }
    });
});