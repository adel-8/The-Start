document.addEventListener('DOMContentLoaded', function () {

    const revenueCanvas = document.getElementById('revenueChart');
    const ordersCanvas  = document.getElementById('ordersChart');

    if (!revenueCanvas || !ordersCanvas) return;

    // Blade passes two separate data attributes:
    //   data-revenue  → [{date, total}, ...]
    //   data-orders   → [{date, count}, ...]
    const revenueRaw = revenueCanvas.dataset.revenue;
    const ordersRaw  = ordersCanvas.dataset.orders;

    if (!revenueRaw || !ordersRaw) {
        console.error('dashboard.js: missing data-revenue or data-orders attribute');
        return;
    }

    let revenueData, ordersData;
    try {
        revenueData = JSON.parse(revenueRaw);
        ordersData  = JSON.parse(ordersRaw);
    } catch (e) {
        console.error('dashboard.js: failed to parse chart data', e);
        return;
    }

    const dates        = revenueData.map(item => item.date);
    const revenueVals  = revenueData.map(item => item.total);
    const ordersVals   = ordersData.map(item => item.count);

    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';

    function formatCurrency(value) {
        return `${currencySymbol} ${Number(value).toFixed(2)}`;
    }

    // ===== REVENUE CHART =====
    new Chart(revenueCanvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: `Revenue (${currencySymbol})`,
                data: revenueVals,
                borderColor: 'var(--color-primary)',
                backgroundColor: 'rgba(100, 95, 125, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: ctx => formatCurrency(ctx.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: val => `${currencySymbol} ${val}`
                    }
                }
            }
        }
    });

    // ===== ORDERS CHART =====
    new Chart(ordersCanvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Number of Orders',
                data: ordersVals,
                backgroundColor: 'rgba(224, 184, 84, 0.7)',
                borderColor: 'var(--color-accent)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        callback: val => Number.isInteger(val) ? val : ''
                    }
                }
            }
        }
    });

});