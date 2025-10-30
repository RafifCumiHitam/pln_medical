// Set global Chart.js defaults
if (typeof Chart !== 'undefined' && Chart.defaults) {
    Chart.defaults.font.family = 'Nunito';
    Chart.defaults.color = '#858796';
} else {
    console.warn('Chart.js not loaded, skipping defaults configuration');
}

const ctxPie = document.getElementById('categoryPieChart');
if (ctxPie && Object.keys(dashboardData.categoryData).length > 0) {
    // Destroy existing chart instance if it exists
    if (window.myPieChart instanceof Chart) {
        window.myPieChart.destroy();
    }

    window.myPieChart = new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: Object.keys(dashboardData.categoryData),
            datasets: [{
                data: Object.values(dashboardData.categoryData),
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(231, 74, 59, 0.8)'
                ],
                hoverBackgroundColor: [
                    'rgba(78, 115, 223, 1)',
                    'rgba(28, 200, 138, 1)',
                    'rgba(54, 185, 204, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(231, 74, 59, 1)'
                ],
                hoverBorderColor: 'rgba(234, 236, 244, 1)',
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            cutout: '80%'
        }
    });
} else {
    console.warn('Pie chart not rendered: No category data available', { categoryData: dashboardData.categoryData });
}