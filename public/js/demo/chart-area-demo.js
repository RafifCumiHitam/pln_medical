// chart-area-demo.js

// Pastikan Chart.js sudah loaded
if (typeof Chart === 'undefined') {
    console.error('Chart.js belum dimuat.');
} else {
    // Set default font dan warna global Chart.js
    Chart.defaults.font.family = 'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#858796';

    // Ambil canvas
    const ctx = document.getElementById('visitorAreaChart');

    // Pastikan data tersedia
    if (ctx && typeof dashboardData !== 'undefined' && Array.isArray(dashboardData.labels) && Array.isArray(dashboardData.data)) {

        // Hapus chart sebelumnya (kalau ada)
        if (window.myAreaChart instanceof Chart) {
            window.myAreaChart.destroy();
        }

        // Buat chart baru
        window.myAreaChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dashboardData.labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    lineTension: 0.3,
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointRadius: 3,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHitRadius: 10,
                    pointHoverBorderWidth: 2,
                    data: dashboardData.data,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: { left: 10, right: 25, top: 25, bottom: 0 }
                },
                scales: {
                    x: {
                        type: 'category',
                        grid: { display: false, drawBorder: false },
                        ticks: {
                            maxTicksLimit: Math.min(dashboardData.labels.length, 10),
                            callback: function(value, index) {
                                return index % Math.ceil(dashboardData.labels.length / 7) === 0 ? dashboardData.labels[index] : '';
                            }
                        }
                    },
                    y: {
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            beginAtZero: true
                        },
                        grid: {
                            color: 'rgb(234, 236, 244)',
                            zeroLineColor: 'rgb(234, 236, 244)',
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                return (value !== null && value !== undefined)
                                    ? `Kunjungan: ${value}`
                                    : '';
                            }
                        }
                    }
                },
                interaction: { mode: 'index', intersect: false },
                hover: { mode: 'nearest', intersect: true }
            }
        });
    } else {
        console.warn('Chart gagal diinisialisasi. Data tidak valid:', dashboardData);
    }
}
