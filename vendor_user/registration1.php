<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Markets</title>
    <script src="schedule.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
        <h1>Inspection Application</h1>
        <form id="inspectionApplication">
            <input class="form-control my-3 rounded-3" type="text" id="marketNameInput" name="marketName" placeholder="Enter Market Name" required>
            <input class="form-control my-3 rounded-3" type="text" id="marketLocationInput" name="marketLocation" placeholder="Enter Market Location" required>
            <select class="form-select" id="marketCategoryInput" name="marketCategory" required>
                <option selected disabled>Category</option>
                <option value="Fish">Fish</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
            </select>
            <button class="btn btn-success m-3 rounded-3" type="submit" name="submit">Add Market</button>
        </form>
        
        <h2 class="mt-4">History of Applications</h2>
        <div class="m-3 border rounded-2" style="height: 30vh; overflow: auto;">
            <table id="applicationHistoryTable" class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Market Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Location</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <?php
    if($_SESSION["profile"] == "Vendor"){
        echo'
    <div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
        <h1>Inspection Schedule</h1>
        <div class="col-sm-4 col-md-3 col-lg-3">
            <input class="form-control my-3 rounded-3" type="text" id="searchMarket" placeholder="Search Market">
        </div>
        <form id="inspectionScheduleForm">
            <input class="form-control my-3 rounded-3" type="text" id="scheduleMarketName" placeholder="Enter Market Name" required>
            <input class="form-control my-3 rounded-3" type="text" id="scheduleMarketLocation" placeholder="Enter Market Location" required>
            <select class="form-select" id="scheduleMarketCategory" required>
                <option selected disabled>Category</option>
                <option value="Fish">Fish</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
            </select>
            <input class="form-control my-3 rounded-3" type="date" id="inspectionDate" required>
            <input class="form-control my-3 rounded-3" type="time" id="inspectionTime" required>
            <button class="btn btn-success m-3 rounded-3" type="submit">Add Schedule</button>
        </form>
    </div>

    <div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
        <h1>Schedule List</h1>
        <div class="m-3 border rounded-2" style="height: 50vh; overflow: auto;">
            <table id="scheduleTable" class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Market</th>
                        <th scope="col">Category</th>
                        <th scope="col">Location</th>
                        <th scope="col">Date</th>
                        <th scope="col">Time</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div> ';
    };
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const applicationHistoryTable = document.querySelector('#applicationHistoryTable tbody');
            const scheduleTable = document.querySelector('#scheduleTable tbody');
            
            const inspectionApplicationForm = document.getElementById('inspectionApplication');
            const inspectionScheduleForm = document.getElementById('inspectionScheduleForm');
            
            const marketApplications = JSON.parse(localStorage.getItem('marketApplications')) || [];

            function updateApplicationHistoryTable() {
                applicationHistoryTable.innerHTML = '';
                marketApplications.forEach((market) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${market.name}</td>
                        <td>${market.category}</td>
                        <td>${market.location}</td>
                    `;
                    applicationHistoryTable.appendChild(row);
                });
            }

            function updateScheduleTable(market, date, time) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${market.name}</td>
                    <td>${market.category}</td>
                    <td>${market.location}</td>
                    <td>${date}</td>
                    <td>${time}</td>
                `;
                scheduleTable.appendChild(row);
            }

            inspectionApplicationForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const name = document.getElementById('marketNameInput').value;
                const location = document.getElementById('marketLocationInput').value;
                const category = document.getElementById('marketCategoryInput').value;

                const newMarketApplication = { name, location, category };
                marketApplications.push(newMarketApplication);
                localStorage.setItem('marketApplications', JSON.stringify(marketApplications));
                
                updateApplicationHistoryTable();
                inspectionApplicationForm.reset();
            });

            inspectionScheduleForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const name = document.getElementById('scheduleMarketName').value;
                const location = document.getElementById('scheduleMarketLocation').value;
                const category = document.getElementById('scheduleMarketCategory').value;
                const date = document.getElementById('inspectionDate').value;
                const time = document.getElementById('inspectionTime').value;

                updateScheduleTable({ name, location, category }, date, time);
                inspectionScheduleForm.reset();
            });

            updateApplicationHistoryTable();
        });
    </script>
</body>
</html>


