<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Report Generation</title>
    <link rel="stylesheet" href="reports.css">
</head>
<body>
    <div class="container bg-white text-dark rounded-3 shadow my-3 p-3">
        <div class="d-flex justify-content-center">
            <h1>Inspection Report Generation</h1>
        </div>

        <form id="report-form">
            <div>
                <label class="form-label" for="market-name">Market Name:</label>
                <input class="form-control mb-3 rounded-3" type="text" id="market-name" placeholder="Enter market name" required>
            </div>
            <div>
                <label class="form-label" for="inspection-date">Inspection Date:</label>
                <input class="form-control mb-3 rounded-3" type="date" id="inspection-date" required>
            </div>
            <div>
                <label class="form-label" for="inspector-name">Inspector Name:</label>
                <input class="form-control mb-3 rounded-3" type="text" id="inspector-name" placeholder="Enter inspector's name" required>
            </div>
            <div class="d-flex justify-content-end">
                <button class="btn btn-success m-3 rounded-3" type="submit">Generate Report</button>
            </div>
        </form>

        <h2 class="mt-4">Generated Reports</h2>
        <div id="reports-container" class="m-3 border rounded-2" style="height: 50vh; overflow: auto;">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Market Name</th>
                        <th>Inspection Date</th>
                        <th>Inspector Name</th>
                    </tr>
                </thead>
                <tbody id="report-history"></tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reportForm = document.getElementById('report-form');
            const reportHistory = document.getElementById('report-history');
            let reports = JSON.parse(localStorage.getItem('inspectionReports')) || [];

            function updateReportHistory() {
                reportHistory.innerHTML = '';
                reports.forEach((report) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${report.marketName}</td>
                        <td>${report.inspectionDate}</td>
                        <td>${report.inspectorName}</td>
                    `;
                    reportHistory.appendChild(row);
                });
            }

            reportForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const marketName = document.getElementById('market-name').value;
                const inspectionDate = document.getElementById('inspection-date').value;
                const inspectorName = document.getElementById('inspector-name').value;
                
                const newReport = { marketName, inspectionDate, inspectorName };
                reports.push(newReport);
                localStorage.setItem('inspectionReports', JSON.stringify(reports));

                updateReportHistory();
                reportForm.reset();
            });

            updateReportHistory();
        });
    </script>
</body>
</html>

