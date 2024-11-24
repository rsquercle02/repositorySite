<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .history-section {
            max-height: 70vh;
            overflow-y: auto;
        }
        .inspection-card {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    
    <div class="container bg-white text-dark rounded-3 shadow my-3 p-3">
        <h1 class="text-center">Market Inspection List</h1>

        <!-- Input Form Section -->
        <div class="my-4">
            <h4>Add New Inspection</h4>
            <form id="inspection-form" class="row gy-2 gx-3 align-items-center">
                <div class="col-md-4">
                    <input class="form-control mb-3 rounded-3" type="text" id="newMarketName" placeholder="Enter Market Name" required>
                </div>
                <div class="col-md-4">
                    <select class="form-select mb-3" id="newCategory" required>
                        <option value="" disabled selected>Category</option>
                        <option value="Fish">Fish</option>
                        <option value="Meat">Meat</option>
                        <option value="Vegetable">Vegetable</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input class="form-control mb-3 rounded-3" type="date" id="newSchedule" required>
                </div>
                <div class="col-md-4">
                    <select class="form-select mb-3" id="newStatus" required>
                        <option value="" disabled selected>Status</option>
                        <option value="Application">Application</option>
                        <option value="Processing">Processing</option>
                        <option value="Complete">Complete</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Add Inspection</button>
                </div>
            </form>
        </div>

        <!-- Inspection Table -->
        <div class="row">
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table id="inspectionTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Market</th>
                                <th scope="col">Category</th>
                                <th scope="col">Schedule</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic rows will be added here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inspection History Section -->
            <div class="col-lg-4">
                <h3>Inspection History</h3>
                <div id="history-section" class="history-section border rounded-3 p-3">
                    <!-- Dynamic inspection history logs will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Dynamic Table and History -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inspectionTableBody = document.querySelector('#inspectionTable tbody');
            const historySection = document.getElementById('history-section');
            const inspectionForm = document.getElementById('inspection-form');
            
            // Sample inspection data (this would come from a database or API)
            const inspections = [
                { market: 'Fish Market', category: 'Fish', schedule: '2024-11-01', status: 'Application' },
                { market: 'Meat Market', category: 'Meat', schedule: '2024-11-05', status: 'Processing' },
                { market: 'Vegetable Market', category: 'Vegetable', schedule: '2024-11-10', status: 'Complete' },
            ];

            // Function to populate the table
            function populateTable() {
                inspectionTableBody.innerHTML = '';
                inspections.forEach((inspection) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${inspection.market}</td>
                        <td>${inspection.category}</td>
                        <td>${inspection.schedule}</td>
                        <td>${inspection.status}</td>
                    `;
                    inspectionTableBody.appendChild(row);
                });
            }

            // Function to add to history
            function addHistory(market, category, status) {
                const historyCard = document.createElement('div');
                historyCard.classList.add('inspection-card', 'bg-light');
                historyCard.innerHTML = `
                    <h5>${market}</h5>
                    <p><strong>Category:</strong> ${category}</p>
                    <p><strong>Status:</strong> ${status}</p>
                    <small>Logged on: ${new Date().toLocaleString()}</small>
                `;
                historySection.prepend(historyCard);
            }

            // Function to add a new inspection entry
            function addInspection(event) {
                event.preventDefault();
                
                const market = document.getElementById('newMarketName').value;
                const category = document.getElementById('newCategory').value;
                const schedule = document.getElementById('newSchedule').value;
                const status = document.getElementById('newStatus').value;

                // Add the new inspection entry to the inspections array
                const newInspection = { market, category, schedule, status };
                inspections.push(newInspection);

                // Add to table and history
                populateTable();
                addHistory(market, category, status);

                // Clear the form fields
                inspectionForm.reset();
            }

            // Add event listener for form submission
            inspectionForm.addEventListener('submit', addInspection);

            populateTable(); // Initially populate the table
        });
    </script>
</body>
</html>

