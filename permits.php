<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit Application Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

    <div class="container w-75 bg-white text-dark rounded-3 shadow my-3 p-3">
        <div class="d-flex justify-content-center">
            <h1>Permit Tracker</h1>
        </div>

        <form id="permit-form">
            <label class="form-label" for="market-name">Market Name:</label>
            <input class="form-control mb-3 rounded-3" type="text" id="market-name" placeholder="Enter market name" required>

            <label class="form-label" for="applicant-name">Applicant Name:</label>
            <input class="form-control mb-3 rounded-3" type="text" id="applicant-name" placeholder="Enter applicant's name" required>

            <label class="form-label" for="application-date">Application Date:</label>
            <input class="form-control mb-3 rounded-3" type="date" id="application-date" required>

            <label class="form-label" for="permit-type">Permit Type:</label>
            <select class="form-select mb-3" id="permit-type" required>
                <option selected disabled>Select Permit Type</option>
                <option value="Trade Permit">Trade Permit</option>
                <option value="Building Permit">Building Permit</option>
                <option value="Health Permit">Health Permit</option>
                <option value="Environmental Permit">Environmental Permit</option>
            </select>

            <div class="d-flex justify-content-end">
                <button class="btn btn-success m-3 rounded-3" type="submit">Track</button>
            </div>
        </form>

        <h3 class="mt-4">Permit Application History</h3>
        <div id="applications-container" class="m-3 border rounded-2" style="height: 50vh; overflow: auto;">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Market Name</th>
                        <th>Applicant Name</th>
                        <th>Application Date</th>
                        <th>Permit Type</th>
                    </tr>
                </thead>
                <tbody id="application-history"></tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const permitForm = document.getElementById('permit-form');
            const applicationHistory = document.getElementById('application-history');
            let applications = JSON.parse(localStorage.getItem('permitApplications')) || [];

            function updateApplicationHistory() {
                applicationHistory.innerHTML = '';
                applications.forEach((application) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${application.marketName}</td>
                        <td>${application.applicantName}</td>
                        <td>${application.applicationDate}</td>
                        <td>${application.permitType}</td>
                    `;
                    applicationHistory.appendChild(row);
                });
            }

            permitForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const marketName = document.getElementById('market-name').value;
                const applicantName = document.getElementById('applicant-name').value;
                const applicationDate = document.getElementById('application-date').value;
                const permitType = document.getElementById('permit-type').value;

                const newApplication = { marketName, applicantName, applicationDate, permitType };
                applications.push(newApplication);
                localStorage.setItem('permitApplications', JSON.stringify(applications));

                updateApplicationHistory();
                permitForm.reset();
            });

            updateApplicationHistory();
        });
    </script>
</body>
</html>

