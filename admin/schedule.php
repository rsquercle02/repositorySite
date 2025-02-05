<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Schedule</title>
    <link rel="stylesheet" href="schedule.css">
</head>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f7f7f7;
    }


    .calendar-container,
    .form-container {
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 10px;
    }

    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        margin-top: 10px;
    }

    .day {
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s, background-color 0.3s ease;
    }

    .day:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .day.selected{
        background-color: rgba(25,135,84, 0.2) !important;
    }

    .day.full {
        background-color: #f8d7da;
        color: #721c24;
        cursor: not-allowed;
    }

    .day-header {
        font-size: 1.2em;
        font-weight: bold;
        color: #333;
    }

    .slots {
        margin-top: 5px;
        font-size: 0.9em;
        color: #28a745;
    }

    .day-names {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }

        .calendar-container,
        .form-container {
            margin-bottom: 20px;
        }

        .slots {
            display: none; /* Hide slots */
        }

        .fullSlot{
            display: none;
        }
    }
</style>
<body>
    <div class="container mb-3 shadow p-3 schedulecon" id="schedulecon">
        <h1>Schedule</h1>
        <div class="col-sm-4 col-md-3 col-lg-3">
            <input class="form-control my-3 rounded-3" type="text" id="searchMarket" placeholder="Search Market">
        </div>
        <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
        <table id="marketsTable" class="table table-hover">
        <thead>
        <tr class="sticky-top">
        <th scope="col">Id</th>
        <th scope="col">Name</th>
        <th scope="col">Description</th>
        <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
    </div>

    <!-- <div class="modal fade w-125" id="verificationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Verification</h1>
                    <button type="button" id="closeBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalContent">
                        <div class="align">
                        <h3 id="businessName">Capstone Store</h3>
                        <label id="hiddenLabel" hidden></label>
                        <div class="mb-2">
                        <p class="inline">Type:</p> <p id="businessType" class="store-type inline">Food Service Establishments</p>
                        </div>
                        </div>
                        <h5>Barangay Clearance</h5>
                        <div class="mb-2" id="barangayClearance"></div>
                        <h5>Business Permit</h5>
                        <div class="mb-2 embedcon" id="businessPermit"></div>
                        <h5>Occupancy Certificate</h5>
                        <div class="mb-2" id="occupancyCertificate"></div>
                        <h5>Tax Certificate</h5>
                        <div class="mb-2" id="taxCertificate"></div>
                    </div>
                    <embed src="calendarSlot.php" style="width: 100%; height: 500px">

                </div>
                    <div class="modal-footer">
                        <button type="button" id="denyBtn" class="btn btn-secondary">Deny</button>
                        <button type="button" id="approveBtn" class="btn btn-success rounded-3">Approve</button>
                    </div>
            </div>
        </div>
    </div> -->

    <div class="container mb-3 shadow p-3 calendarcon" id="calendarcon">
        <button id="backbtn" class="btn btn-primary">< Back</button>
        <div class="container border">
        <div class="row">
            <!-- Calendar Section -->
            <div class="col-md-9 mt-3 container">
                <h2 class="text-center">Inspection schedule</h2>
                <div class="text-center mb-3">
                    <button id="prev-month" class="btn btn-primary"><</button>
                    <h4 id="current-month-year" class="mx-3 d-inline"></h4>
                    <button id="next-month" class="btn btn-primary">></button>
                </div>
                <div id="day-names" class="day-names"></div>
                <div id="calendar" class="calendar"></div>
            </div>

            <!-- Form Section -->
            <div class="col-md-3 pt-3 container">
                <h4>Schedule Form</h4>
                <form id="booking-form">
                    <div class="form-group mb-3">
                        <p id="storeId">Cap101</p>
                    </div>
                    <div class="form-group mb-3">
                        <p id="storeName">Capstone Store</p>
                    </div>
                    <div class="form-group mb-3">
                        <p id="storeLocation">Cap 101 Capstone City Capstone Store</p>
                    </div>
                    <div class="form-group mb-3">
                        <p id="inspectionDate">Select Date.</p>
                        <div id="insDateErr" class="form-text error"></div>
                    </div>
                    <div class="form-group mb-3">
                        <p id="dayOfWeek">Day of week.</p>
                        <div id="dayWeekErr" class="form-text error"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="inspectionTime">Inspection Time:</label>
                        <input type="time" class="form-control" id="inspectionTime" name="inspectionTime" placeholder="Your Name" required>
                        <div id="insTimeErr" class="form-text error"></div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="inspectors">Inspectors:</label>
                        <select id="dropdown1" class="form-select mb-2" required>
                            <option selected value="" disabled>Inspector 1</option>
                        </select>
                        <div id="dropdown1Err" class="form-text error"></div>
                        <select id="dropdown2" class="form-select" required>
                            <option selected value="" disabled>Inspector 2</option>
                        </select>
                        <div id="dropdown2Err" class="form-text error"></div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Book Now</button>
                </form>
            </div>
        </div>
    </div>
    </div>
</body>

<script src="schedule.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<!-- Calendar script -->
<script>
        const bookings = ["2025-01-20", "2025-01-20", "2025-01-22", "2025-01-25", "2025-01-25", "2025-01-25", "2025-01-25" ,"2025-01-25", "2025-02-25", "2025-02-25", "2025-02-25", "2025-02-25" ,"2025-02-25", ];
        const slotsPerDay = 5;
        const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        let currentDate = new Date();

        function updateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDayOfMonth = new Date(year, month, 1).getDay();

            document.getElementById("current-month-year").textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;
            const dayNamesContainer = document.getElementById("day-names");

            function isMobile() {
                return window.innerWidth <= 992;
            }

            function updateDayNames() {
                const displayedDayNames = isMobile()
                    ? dayNames.map(day => day.substring(0, 3))
                    : dayNames;

                dayNamesContainer.innerHTML = displayedDayNames.map(day => `<div class="dayName bg-success-subtle text-success rounded-2">${day}</div>`).join("");
            }

            updateDayNames();
            window.addEventListener("resize", updateDayNames);

            const calendar = document.getElementById("calendar");
            calendar.innerHTML = "";

            for (let i = 0; i < firstDayOfMonth; i++) calendar.innerHTML += `<div></div>`;
            for (let day = 1; day <= daysInMonth; day++) {
                const formattedDate = `${year}-${String(month + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
                const count = bookings.filter(date => date === formattedDate).length;
                const isFull = count >= slotsPerDay;
                calendar.innerHTML += `
                    <div class="day${isFull ? " full bg-dangerous" : " bg-info bg-opacity-10"} border border-info container" onclick="selectDate('${formattedDate}', ${isFull})">
                        <div class="day-header">${day}</div>
                        ${isFull ? '<span class="text-danger fullSlot">Full</span>' : `<div class="slots">${slotsPerDay - count} slots</div>`}
                    </div>`;
            }
        }

        function selectDate(date, isFull) {
            const previouslySelected = document.querySelector('.day.selected');
            if (previouslySelected) {
                previouslySelected.classList.remove('selected');
            }

            if (isFull) {
                alert("The slot is full.");
                document.getElementById("inspectionDate").textContent = "Select new date.";
                document.getElementById("dayOfWeek").textContent = "Day of week.";
            } else {
                const selectedDate = new Date(date);
                document.getElementById("inspectionDate").textContent = date;
                document.getElementById("dayOfWeek").textContent = dayNames[selectedDate.getDay()];
                event.target.classList.add('selected');

                // Highlight the selected date
                const selectedDateElement = Array.from(document.querySelectorAll('.day')).find(
                    (dayElement) => dayElement.querySelector('.day-header')?.textContent == new Date(date).getDate()
                );
                
                if (selectedDateElement) {
                        selectedDateElement.classList.add('selected');
                }

            }
        }

        document.getElementById("prev-month").addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            updateCalendar();
        });

        document.getElementById("next-month").addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            updateCalendar();
        });

        updateCalendar();

        // List of car brands
        function inspectorsList(dayName){
            // Example URL for fetching detailed information (you may adjust it)
            const detailsUrl = `http://localhost:8001/api-gateway/public/inspection/inspector/${assignedDay}`;
            fetch(detailsUrl)
                .then(response => response.json())
                .then(data => {
                    return data;
                })
                .catch(error => console.error('Error fetching data:', error));
        }
        const carBrands = ["Toyota", "Honda", "Ford", "BMW", "Tesla", "Chevrolet", "Monday", "Tuesday", "Wednesday", "One", "Two", "Three",];

        // Dropdown elements
        const dropdown1 = document.getElementById('dropdown1');
        const dropdown2 = document.getElementById('dropdown2');

        // Function to populate dropdowns
        function populateDropdowns() {
            carBrands.forEach(brand => {
                const option1 = document.createElement('option');
                const option2 = document.createElement('option');
                option1.value = brand;
                option1.textContent = brand;
                option2.value = brand;
                option2.textContent = brand;
                dropdown1.appendChild(option1);
                dropdown2.appendChild(option2);
            });
        }

        // Function to update options dynamically
        function updateDropdownOptions() {
            const selected1 = dropdown1.value;
            const selected2 = dropdown2.value;

            // Clear both dropdowns
            dropdown1.innerHTML = '<option value="">Inspector 1</option>';
            dropdown2.innerHTML = '<option value="">Inspector 2</option>';

            carBrands.forEach(brand => {
                if (brand !== selected2) {
                    const option1 = document.createElement('option');
                    option1.value = brand;
                    option1.textContent = brand;
                    if (brand === selected1) option1.selected = true;
                    dropdown1.appendChild(option1);
                }

                if (brand !== selected1) {
                    const option2 = document.createElement('option');
                    option2.value = brand;
                    option2.textContent = brand;
                    if (brand === selected2) option2.selected = true;
                    dropdown2.appendChild(option2);
                }
            });
        }

        // Event listeners for dropdown changes
        dropdown1.addEventListener('change', updateDropdownOptions);
        dropdown2.addEventListener('change', updateDropdownOptions);

        // Initialize dropdowns
        populateDropdowns();
</script>
</html>