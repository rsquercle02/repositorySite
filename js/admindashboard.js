// Fetch data from the API endpoint
detailsUrl = `http://localhost:8001/api/service/integration/InfoTally`;
fetch(detailsUrl)
  .then(response => response.json())
  .then(data => {
    //Bar chart
    // Convert JSON data to an array of { label, value } objects
    let combinedData = Object.entries(data[0]).map(([key, value]) => {
        // Optional: convert snake_case keys to readable labels
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        return {
          label: label,
          value: parseInt(value || 0)
        };
      });
  
      // Sort the combined data from highest to lowest value
      combinedData.sort((a, b) => b.value - a.value);
  
      // Extract sorted labels and values
      const sortedLabels = combinedData.map(item => item.label);
      const sortedValues = combinedData.map(item => item.value);
  
      // Generate color dynamically with higher contrast
      const getColor = (value, index) => {
        // Set distinct color based on index, for high contrast
        const hue = (index * 50) % 360;  // Cycle through different hues for contrast
        return `hsl(${hue}, 80%, 60%)`; // Using higher saturation (80%) and lightness (60%)
      };
  
      // Bar chart configuration
      const chartData = {
        labels: sortedLabels,
        datasets: [{
          label: 'Violation Counts',
          data: sortedValues,
          backgroundColor: sortedValues.map((value, index) => getColor(value, index)),  // Distinct colors for each bar
          borderColor: sortedValues.map((value, index) => getColor(value, index)),  // Same color for borders
          borderWidth: 2
        }]
      };
  
      const ctx = document.getElementById('barChart').getContext('2d');

const chart = new Chart(ctx, {
  type: 'bar',
  data: chartData,
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: false
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            return ` ${context.parsed.y} violations`;
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1
        }
      },
      x: {
        ticks: {
          display: window.innerWidth > 768 // initial value
        },
        grid: {
          display: window.innerWidth > 768 // initial value
        }
      }
    }
  }
});

// Function to update chart options on resize
function updateChartForScreenSize() {
  const isMobile = window.innerWidth <= 768;
  chart.options.scales.x.ticks.display = !isMobile;
  chart.options.scales.x.grid.display = !isMobile;
  chart.update();
}

// Listen for window resize
window.addEventListener('resize', updateChartForScreenSize);


      //Category chart
      // Convert JSON data to an array of { label, value } objects
    let combinedcategoryData = Object.entries(data[6]).map(([key, value]) => {
        // Convert snake_case keys to readable labels
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        return {
        label: label,
        value: parseInt(value || 0)
        };
    });
    
    // Sort the combined data from highest to lowest value
    combinedcategoryData.sort((a, b) => b.value - a.value);
    
    // Extract sorted labels and values
    const sortedcategoryLabels = combinedcategoryData.map(item => item.label);
    const sortedcategoryValues = combinedcategoryData.map(item => item.value);
    
    // Generate color dynamically with higher contrast
    const getcategoryColor = (value, index) => {
        // Set distinct color based on index, for high contrast
        const hue = (index * 120) % 360;  // 3 categories = spread by 120° around the hue circle
        return `hsl(${hue}, 80%, 60%)`; // Vivid and contrasting
    };
    
    // Bar chart configuration
    const chartcategoryData = {
        labels: sortedcategoryLabels,
        datasets: [{
        label: 'Risk Category Counts',
        data: sortedcategoryValues,
        backgroundColor: sortedcategoryValues.map((value, index) => getColor(value, index)),
        borderColor: sortedcategoryValues.map((value, index) => getColor(value, index)),
        borderWidth: 2
        }]
    };
    
    // Initialize the chart
    new Chart(document.getElementById('categoryChart').getContext('2d'), {
        type: 'bar',
        data: chartcategoryData,
        options: {
        responsive: true,
        plugins: {
            legend: {
            display: false
            },
            tooltip: {
            callbacks: {
                label: function(context) {
                return ` ${context.parsed.y} reports`;
                }
            }
            }
        },
        scales: {
            y: {
            beginAtZero: true,
            stepSize: 1
            }
        }
        }
    });


    document.getElementById('storeTotal').innerText = data[1].store_count;
    document.getElementById('reportResolved').innerText = data[2].report_resolved;
    document.getElementById('reportCreated').innerText = data[3].report_created;
    document.getElementById('clrngopsTotal').innerText = data[5].clrngops_total;

    // Report categories
    const reportcategories = [
        'Report created', 'Report resolved', 'Report total'
    ];

    const statusCounts = [
        'reportcreated_count', 'reportresolved_count', 'report_total'
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
