// Bar Chart Initialization
const barChartCtx = document.getElementById('barChart').getContext('2d');
const barChart = new Chart(barChartCtx, {
    type: 'bar',
    data: {
        labels: ['Total Market', 'Total Approved', 'Total Failed', 'Users'],
        datasets: [{
            label: 'Number of Applications',
            data: [50, 45, 5, 50], // Example data
            backgroundColor: ['#ff6666', '#66b3ff', '#ffcc66', '#ffff66'],
            borderColor: ['#ff3333', '#3399ff', '#ff9933', '#ffff33'],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Doughnut Chart Initialization
const doughnutChartCtx = document.getElementById('doughnutChart').getContext('2d');
const doughnutChart = new Chart(doughnutChartCtx, {
    type: 'doughnut',
    data: {
        labels: ['Total Market', 'Total Approved', 'Total Failed', 'Users'],
        datasets: [{
            label: 'Application Status',
            data: [50, 45, 5, 45], // Example data
            backgroundColor: ['#ff6666', '#66b3ff', '#ffcc66', '#ffff66']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2
    }
});
