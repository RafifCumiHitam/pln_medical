// Set global Chart.js defaults
if (typeof Chart !== 'undefined' && Chart.defaults) {
    Chart.defaults.font.family = 'Nunito';
    Chart.defaults.color = '#858796';
} else {
    console.warn('Chart.js not loaded, skipping defaults configuration');
}

const ctxBar = document.getElementById('stockBarChart');
if (ctxBar && Array.isArray(dashboardData.medicineNames) && Array.isArray(dashboardData.stockLevels)) {
    // Destroy existing chart instance if it exists
    if (window.myBarChart instanceof Chart) {
        window.myBarChart.destroy();
    }

    window.myBarChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: dashboardData.medicineNames,
            datasets: [{
                label: 'Stok Tersedia',
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                data: dashboardData.stockLevels,
                hoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                hoverBorderColor: 'rgba(78, 115, 223, 1)',
            }],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        maxTicksLimit: 5
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
} else {
    console.warn('Bar chart not rendered: Invalid data or missing canvas', { medicineNames: dashboardData.medicineNames, stockLevels: dashboardData.stockLevels });
}