// Fetch data from the API endpoint
detailsUrl = `http://localhost:8001/api/service/concernslist/ViolationTally`;
fetch(detailsUrl)
  .then(response => response.json())
  .then(data => {
    // Categories and corresponding data keys
    const categories = [
      'Expired Products', 'Unhygienic Conditions', 'Incorrect Labelling',
      'Overpricing', 'Unsanitary Storage', 'Misleading Advertisement',
      'Improper Packaging', 'Lack of Proper License', 'Unsafe Food Handling', 'Resolved'
    ];

    const violationCounts = [
      'expired_products_count', 'unhygienic_conditions_count', 'incorrect_labelling_count',
      'overpricing_count', 'unsanitary_storage_count', 'misleading_advertisement_count',
      'improper_packaging_count', 'lack_of_proper_license_count', 'unsafe_food_handling_count', 'no_violations'
    ];

    // Bar chart configuration using fetched data
    const chartData = {
      labels: categories,
      datasets: [{
        label: 'Violation Counts',
        data: violationCounts.map(key => parseInt(data[0][key] || 0)),  // Directly accessing the data from the fetched response
        backgroundColor: ['#ff6666', '#66b3ff', '#ffcc66', '#ffff66', '#ff6666', '#66b3ff', '#ffcc66', '#ffff66', '#ffcc66', '#ffff66'],
        borderColor: ['#ff3333', '#3399ff', '#ff9933', '#ffff33', '#ff6666', '#66b3ff', '#ffcc66', '#ffff66', '#ffcc66', '#ffff66'],
        borderWidth: 1
      }]
    };

    // Initialize the chart
    new Chart(document.getElementById('barChart').getContext('2d'), {
      type: 'bar',
      data: chartData,
      options: { scales: { y: { beginAtZero: true } } }
    });

    document.getElementById('totalStore').innerText = data[1].store_count;
    document.getElementById('resolvedReport').innerText = data[2].resolved_report;
    document.getElementById('fwrdCityReport').innerText = data[3].fwrdcity_report;
    document.getElementById('totalClrngops').innerText = data[5].clrngops_count;

    // Report categories
    const reportcategories = [
        'No action', 'Forward to captain', 'Froward to cityhall',
        'Resolved'
    ];

    const statusCounts = [
        'noaction_count', 'fwrdcaptain_count', 'fwrdcityhall_count', 'resolved_count'
    ];

    // Doughnut Chart Initialization
    const doughnutChartCtx = document.getElementById('doughnutChart').getContext('2d');
    const doughnutChart = new Chart(doughnutChartCtx, {
        type: 'doughnut',
        data: {
            labels: reportcategories,
            datasets: [{
                label: 'Report status',
                data: statusCounts.map(key => parseInt(data[4][key] || 0)),  // Directly accessing the data from the fetched response
                backgroundColor: ['#ff6666', '#66b3ff', '#ffcc66', '#ffff66']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2
        }
    });
  })
  .catch(error => console.error('Error fetching data:', error));

// Bar Chart Initialization
/*
const barChartCtx = document.getElementById('barChart').getContext('2d');
const barChart = new Chart(barChartCtx, {
    type: 'bar',
    data: {
        labels: ['Total Market', 'Total Approved', 'Total Failed', 'Users', 'Total Market', 'Total Approved', 'Total Failed', 'Users', 'Total Failed', 'Users'],
        datasets: [{
            label: 'Number of Applications',
            data: [50, 45, 5, 50, 50, 45, 5, 50, 5, 50], // Example data
            backgroundColor: ['#ff6666', '#66b3ff', '#ffcc66', '#ffff66', '#ff6666', '#66b3ff', '#ffcc66', '#ffff66', '#ffcc66', '#ffff66'],
            borderColor: ['#ff3333', '#3399ff', '#ff9933', '#ffff33', '#ff6666', '#66b3ff', '#ffcc66', '#ffff66', '#ffcc66', '#ffff66'],
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
}); */

// Doughnut Chart Initialization
const doughnutChartCtx = document.getElementById('doughnutChart').getContext('2d');
const doughnutChart = new Chart(doughnutChartCtx, {
    type: 'doughnut',
    data: {
        labels: reportcategory,
        datasets: [{
            label: 'Report status',
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
