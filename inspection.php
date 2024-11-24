<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .history-section {
            max-height: 50vh;
            overflow-y: auto;
        }
        .schedule-card, .history-card {
            margin-bottom: 1rem;
            padding: 1rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    
    <div class="container bg-white text-dark rounded-3 shadow mt-3 p-4">
        <h1 class="text-center mb-4">Inspection Schedule</h1>

        <!-- Input Form Section -->
        <div class="my-4">
            <h4>Add New Schedule</h4>
            <form id="schedule-form" class="row gy-2 gx-3 align-items-center">
                <div class="col-md-3">
                    <input class="form-control mb-3 rounded-3" type="text" id="marketName" placeholder="Enter Market Name" required>
                </div>
                <div class="col-md-3">
                    <select class="form-select mb-3 rounded-3" id="category" required>
                        <option value="" disabled selected>Category</option>
                        <option value="Fish">Fish</option>
                        <option value="Meat">Meat</option>
                        <option value="Vegetable">Vegetable</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input class="form-control mb-3 rounded-3" type="date" id="scheduleDate" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 rounded-3">Add Schedule</button>
                </div>
            </form>
        </div>

        <!-- Schedule Table -->
        <div class="table-responsive">
            <table id="scheduleTable" class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Market</th>
                        <th scope="col">Category</th>
                        <th scope="col">Schedule</th>
                        <th scope="col">Action</th> <!-- New Action Column -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamic rows will be added here -->
                </tbody>
            </table>
        </div>

        <!-- Schedule History Section -->
        <div class="mt-5">
            <h3>Schedule History</h3>
            <div id="history-section" class="history-section p-3 bg-light rounded-3">
                <!-- Dynamic schedule history logs will appear here -->
            </div>
        </div>

        <!-- Inspection Modal -->
        <div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="inspectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="inspectionModalLabel">Inspection Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="inspection-form">
                            <input type='hidden' id='inspectIndex' />
                            <div class='mb-3'>
                                <label for='inspectionNotes' class='form-label'>Inspection Violations:</label>
                                <textarea class='form-control' id='inspectionNotes' rows='3' required></textarea>
                            </div>

                            <!-- File Upload Input -->
                            <div class='mb-3'>
                                <label for='inspectionPhoto' class='form-label'>Upload Photo:</label>
                                <input type='file' class='form-control' id='inspectionPhoto' accept='image/*' required />
                            </div>

                            <button type='submit' class='btn btn-primary'>Submit Inspection</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- JavaScript for Dynamic Table and History -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scheduleTableBody = document.querySelector('#scheduleTable tbody');
            const historySection = document.getElementById('history-section');
            const scheduleForm = document.getElementById('schedule-form');
            const inspectionModal = new bootstrap.Modal(document.getElementById('inspectionModal'));
            
            // Sample schedule data
            const schedules = [
                { market: 'Central Market', category: 'Fish', schedule: '2024-11-01' },
                { market: 'City Meat Market', category: 'Meat', schedule: '2024-11-05' },
                { market: 'Green Market', category: 'Vegetable', schedule: '2024-11-10' }
            ];

            // Function to populate the schedule table
            function populateTable() {
                scheduleTableBody.innerHTML = '';
                schedules.forEach((schedule, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${schedule.market}</td>
                        <td>${schedule.category}</td>
                        <td>${schedule.schedule}</td>
                        <td><button class='btn btn-warning inspect-btn' data-index='${index}'>Inspect</button></td> <!-- Inspect Button -->
                    `;
                    scheduleTableBody.appendChild(row);
                });

                // Add event listeners to inspect buttons
                document.querySelectorAll('.inspect-btn').forEach(button => {
                    button.addEventListener('click', openInspectionForm);
                });
            }

            // Function to add to history
            function addHistory(market, category, schedule) {
                const historyCard = document.createElement('div');
                historyCard.classList.add('history-card', 'bg-white');
                historyCard.innerHTML = `
                    <h5>${market}</h5>
                    <p><strong>Category:</strong> ${category}</p>
                    <p><strong>Schedule Date:</strong> ${schedule}</p>
                    <small>Logged on: ${new Date().toLocaleString()}</small>
                `;
                historySection.prepend(historyCard);
            }

            // Function to add a new schedule entry
            function addSchedule(event) {
                event.preventDefault();
                
                const market = document.getElementById('marketName').value;
                const category = document.getElementById('category').value;
                const scheduleDate = document.getElementById('scheduleDate').value;

                // Add the new schedule entry to the schedules array
                const newSchedule = { market, category, schedule: scheduleDate };
                schedules.push(newSchedule);

                // Add to table and history
                populateTable();
                addHistory(market, category, scheduleDate);

                // Clear the form fields
                scheduleForm.reset();
            }

            // Function to open the inspection form
            function openInspectionForm(event) {
                const index = event.target.dataset.index;
                
                // Set the index of the inspected item
                document.getElementById('inspectIndex').value = index;

                inspectionModal.show(); // Show the modal
            }

            // Function to handle inspection submission
            function handleInspectionSubmit(event) {
              event.preventDefault();

              const index = document.getElementById('inspectIndex').value;
              const inspectionNotes = document.getElementById('inspectionNotes').value;
              const inspectionPhoto = document.getElementById('inspectionPhoto').files[0];

              // Remove the inspected entry from schedules
              if (index >= 0 && index < schedules.length) {
                  const inspectedSchedule = schedules[index];
                  schedules.splice(index, 1); // Remove from array
                  populateTable(); // Refresh table
                  addHistory(inspectedSchedule.market, inspectedSchedule.category, inspectedSchedule.schedule); // Log to history

                  // Show success alert with inspection notes and file name (if any)
                  alert(`Inspection logged successfully!\nNotes: ${inspectionNotes}\nFile Uploaded: ${inspectionPhoto ? inspectionPhoto.name : 'No file uploaded.'}`);
                  
                  inspectionModal.hide(); // Close modal
              }
              
              // Clear inspection notes field and file input
              document.getElementById('inspectionNotes').value = '';
              document.getElementById('inspectionPhoto').value = '';
          }

          // Add event listener for form submission
          scheduleForm.addEventListener('submit', addSchedule);
          document.getElementById('inspection-form').addEventListener('submit', handleInspectionSubmit);

          populateTable(); // Initially populate the table
      });
    </script>

</body>
</html>
