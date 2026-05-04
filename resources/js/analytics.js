document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.analytics-container');
    if (!container) return;
    const rawData = container.getAttribute('data-analytics');
    if (!rawData) return;

    let data;
    try {
        data = JSON.parse(rawData);
    } catch (e) {
        console.error('Failed to parse analytics data:', e);
        return;
    }

    // Helper: format currency
    const currencySymbol = document.documentElement.dataset.currencySymbol || 'DZD';
    const formatCurrency = (val) => `${currencySymbol} ${parseFloat(val).toFixed(2)}`;

    // ---------- Main Chart ----------
    const mainCtx = document.getElementById('mainChart');
    if (!mainCtx) return;
    const ctx = mainCtx.getContext('2d');

    let currentChartType = 'revenue'; // Track the current chart type

    let mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates || [],
            datasets: [{
                label: `Revenue (${currencySymbol})`,
                data: data.revenueData || [],
                borderColor: 'var(--color-primary)',
                backgroundColor: 'rgba(100,95,125,0.1)',
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
                        label: (ctx) => {
                            if (ctx.dataset.label.includes('Revenue')) {
                                return formatCurrency(ctx.raw);
                            }
                            return ctx.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (val) => {
                            // Format ticks based on current chart type
                            return currentChartType === 'revenue' ? formatCurrency(val) : val;
                        }
                    }
                }
            }
        }
    });

    // ---------- Category Bar Chart ----------
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: data.categoryLabels || [],
                datasets: [{
                    label: `Revenue (${currencySymbol})`,
                    data: data.categoryData || [],
                    backgroundColor: 'rgba(100,95,125,0.6)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => formatCurrency(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (val) => formatCurrency(val)
                        }
                    }
                }
            }
        });
    }

    // ---------- Payment Methods Pie Chart ----------
    const paymentCtx = document.getElementById('paymentChart');
    if (paymentCtx) {
        new Chart(paymentCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: data.paymentLabels || [],
                datasets: [{
                    data: data.paymentData || [],
                    backgroundColor: ['#645F7D', '#E0B854', '#6F6A7A', '#4E3B64']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.raw} orders`
                        }
                    }
                }
            }
        });
    }

    // ---------- Customer Type Donut Chart ----------
    const customerTypeCtx = document.getElementById('customerTypeChart');
    if (customerTypeCtx) {
        new Chart(customerTypeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['New Customers', 'Returning Customers'],
                datasets: [{
                    data: [data.newCustomers || 0, data.returningCustomers || 0],
                    backgroundColor: ['#645F7D', '#E0B854']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.raw}`
                        }
                    }
                }
            }
        });
    }

    // ---------- Chart Type & Range Toggle ----------
    let currentType = 'revenue';
    let currentRange = data.currentRange || '30d';

    function updateMainChart(type, range) {
        currentChartType = type; // Update the tracked chart type
        fetch(`/admin/analytics/data?type=${type}&range=${range}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(newData => {
                if (!mainChart) return;
                mainChart.data.labels = newData.labels || [];
                mainChart.data.datasets[0].data = newData.values || [];

                let label = '';
                if (type === 'revenue') label = `Revenue (${currencySymbol})`;
                else if (type === 'orders') label = 'Orders';
                else label = 'Customers';
                mainChart.data.datasets[0].label = label;

                // Force y-axis to update callback
                mainChart.update();
            })
            .catch(error => console.error('Error fetching chart data:', error));
    }

    // Attach chart type buttons
    const typeBtns = document.querySelectorAll('.chart-type-btn');
    typeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            typeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentType = btn.dataset.type;
            updateMainChart(currentType, currentRange);
        });
    });

    // Attach range buttons
    const rangeBtns = document.querySelectorAll('.range-btn');
    rangeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            rangeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentRange = btn.dataset.range;
            updateMainChart(currentType, currentRange);
        });
    });
});