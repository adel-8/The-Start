document.addEventListener('DOMContentLoaded', function () {

    // ===== GET DATA FROM BLADE =====
    const revenueCanvas = document.getElementById('revenueChart');
    const ordersCanvas = document.getElementById('ordersChart');

    if (!revenueCanvas || !ordersCanvas) return;

    const chartData = JSON.parse(revenueCanvas.dataset.chart);

    const dates = chartData.map(item => item.date);
    const revenueData = chartData.map(item => item.revenue);
    const ordersData = chartData.map(item => item.orders);

    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';

    function formatCurrency(value) {
        return `${currencySymbol} ${Number(value).toFixed(2)}`;
    }

    // ===== REVENUE CHART =====
    const revenueCtx = revenueCanvas.getContext('2d');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: `Revenue (${currencySymbol})`,
                data: revenueData,
                borderColor: 'var(--color-primary)',
                backgroundColor: 'rgba(100, 95, 125, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            return formatCurrency(ctx.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (val) {
                            return `${currencySymbol} ${val}`;
                        }
                    }
                }
            }
        }
    });

    // ===== ORDERS CHART =====
    const ordersCtx = ordersCanvas.getContext('2d');

    new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Number of Orders',
                data: ordersData,
                backgroundColor: 'var(--color-accent)',
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
                        stepSize: 1
                    }
                }
            }
        }
    });

});