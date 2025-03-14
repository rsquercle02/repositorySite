//fetch
function fetchBusiness(){
    const tableBody = document.querySelector("#marketsTable tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    // Fetch data from the API endpoint to populate the table
    fetch('https://bfmsi.smartbarangayconnect.com/api/service/schedule/schedule')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#marketsTable tbody");
            tableBody.innerHTML = ''; // This will remove all the previous rows
            data.forEach(item => {
                const row = document.createElement("tr");

                // Create table data cells dynamically
                const idCell = document.createElement("td");
                idCell.textContent = item.businessId;
                row.appendChild(idCell);

                const nameCell = document.createElement("td");
                nameCell.textContent = item.businessName;
                row.appendChild(nameCell);

                const descriptionCell = document.createElement("td");
                descriptionCell.textContent = item.businessDescription;
                row.appendChild(descriptionCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "Schedule";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#verificationModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    document.getElementById('calendarcon').style.display = 'block';
                    document.getElementById('schedulecon').style.display = 'none';
                    schedulesfetch();
                    storeDetails(item.businessId);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));

}

    //Fetch store details
    function storeDetails(businessId){
        document.getElementById('businessId').textContent = "";
        document.getElementById('storeName').textContent = "";
        document.getElementById('storeLocation').textContent = "";
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/schedule/fetch/${businessId}`;
        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                document.getElementById('businessId').textContent = data.businessId;
                document.getElementById('storeName').textContent = data.businessName;
                document.getElementById('storeLocation').textContent = `${data.streetBuildingHouse} ${data.barangay} ${data.municipality}`;
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    //search item
    function search(searchTerm){
        const tableBody = document.querySelector("#marketsTable tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Fetch data from the API endpoint to populate the table
    fetch(`https://bfmsi.smartbarangayconnect.com/api/service/schedule/schedule/${searchTerm}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#marketsTable tbody");
            tableBody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement("tr");

                // Create table data cells dynamically
                const idCell = document.createElement("td");
                idCell.textContent = item.businessId;
                row.appendChild(idCell);

                const nameCell = document.createElement("td");
                nameCell.textContent = item.businessName;
                row.appendChild(nameCell);

                const descriptionCell = document.createElement("td");
                descriptionCell.textContent = item.businessDescription;
                row.appendChild(descriptionCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "Schedule";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#verificationModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    //fetchItemDetails(item.businessId);
                    document.getElementById('calendarcon').style.display = 'block';
                    document.getElementById('schedulecon').style.display = 'none';
                    storeDetails(item.businessId);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    document.getElementById("searchMarket").addEventListener('input', function(event) {
        //document.getElementById("testkeyup").innerText = "hey";
        const searchTerm = document.getElementById("searchMarket").value.trim();
        if (searchTerm === '') {
            // If the search box is empty, display all the data
            fetchBusiness(); // `data` is your full data array
        } else {
            // Otherwise, update the table with filtered results based on the search term
            search(searchTerm);
        }
    });

    //Calendar function 
    let bookings = [];
    function schedulesfetch(){
    //let bookings = [];
    const detailsUrl = 'https://bfmsi.smartbarangayconnect.com/api/service/schedule/inspectionSchedule';
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            if (data) {
                bookings = data.map(item => item.inspectionDate);
                updateCalendar(bookings);
            }
        })
        .catch(error => console.error('Error fetching data:', error));
    }
    
    //function calendarSlots()
        //let bookings = [];
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

                dayNamesContainer.innerHTML = displayedDayNames.map(day => `<div class="dayName bg-success text-bg rounded">${day}</div>`).join("");
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
                    <div class="day${isFull ? " full" : " notfull"} border border-info container" onclick="selectDate('${formattedDate}', ${isFull})">
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
                //alert("The slot is full.");
                Swal.fire({
                        title: "Slot is full!",
                        text: "Choose other slot.",
                        icon: "error",
                        confirmButtonColor: "#0f0"
                    });
                document.getElementById("inspectionDate").textContent = "Select new date.";
                document.getElementById("assignedDay").textContent = "Day of week.";
            } else {
                const selectedDate = new Date(date);
                document.getElementById("inspectionDate").textContent = date;
                document.getElementById("assignedDay").textContent = dayNames[selectedDate.getDay()];
                event.target.classList.add('selected');
                //const inspectionDate = document.getElementById("inspectionDate").textContent;
                //const assignedDay = document.getElementById("assignedDay").textContent;
                document.getElementById('inspection').style.display = 'block';
                scheduleValidation();        

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

        
    const backBtn = document.getElementById('backBtn');
    backbtn.addEventListener('click', function() {
        document.getElementById('schedulecon').style.display = 'block';
        document.getElementById('calendarcon').style.display = 'none';
        document.getElementById('inspection').style.display = 'none';
        const businessId = document.getElementById('businessId');
        businessId.textContent = 'Business Id';
        const storeName = document.getElementById('storeName');
        storeName.textContent = 'Store Name';
        const storeLocation = document.getElementById('storeLocation');
        storeLocation.textContent = 'Store Location';
        const inspectionDate = document.getElementById('inspectionDate');
        inspectionDate.textContent = 'Inspection Date';
        const assignedDay = document.getElementById('assignedDay');
        assignedDay.textContent = 'Inspection Day';
        const timeFrom = document.getElementById('timeFrom');
        timeFrom.value = '';
        const timeTo = document.getElementById('timeTo');
        timeTo.value = '';
        const monthList = document.getElementById('monthList');
        monthList.innerHTML = '';
        const insTimeErr = document.getElementById('insTimeErr');
        insTimeErr.innerHTML = '';
        const assignedInsErr = document.getElementById('assignedInsErr');
        assignedInsErr.innerHTML = '';
        fetchBusiness();
    });
    
    function crossesMidnight(timeFrom, timeTo) {
        // Append ":00" for seconds if missing
        timeFrom += ":00";
        timeTo += ":00";

        const toMinutes = (time) => {
            let [h, m, s] = time.split(":").map(Number);
            return h * 60 + m + s / 60; // Convert time to total minutes
        };
        
        return toMinutes(timeTo) < toMinutes(timeFrom);
    }

    const scheduleForm = document.getElementById('schedule-form');
        
        scheduleForm.addEventListener('submit', (event) => {
            event.preventDefault();
            let isValid = true;

            const businessId = document.getElementById('businessId').textContent;
            if(businessId === "") {
            document.getElementById('businessIdErr').textContent = 'No store id.';
            isValid = false;
            }

            const inspectionDate = document.getElementById('inspectionDate').textContent;
            if(inspectionDate === "") {
            document.getElementById('inspectionDateErr').textContent = 'Select inspection date.';
            isValid = false;
            }

            const assignedDay = document.getElementById('assignedDay').textContent;
            if(assignedDay === "") {
            document.getElementById('assignedDayErr').textContent = 'Select assigned day.';
            isValid = false;
            }

            const timeFrom = document.getElementById('timeFrom').value;
            if(timeFrom === "") {
            document.getElementById('insTimeErr').textContent = 'Select inspection time.';
            isValid = false;
            }

            const timeTo = document.getElementById('timeTo').value;
            if(timeTo === "") {
            document.getElementById('insTimeErr').textContent = 'Select inspection time.';
            isValid = false;
            }

            const insTimeErr = document.getElementById('insTimeErr').textContent;
            if (insTimeErr.startsWith('Choose new time.')){
                document.getElementById('insTimeErr').textContent = 'Choose new time.';
                isValid = false;
            }

            if (crossesMidnight(timeFrom, timeTo)) {
                insTimeErr.textContent = 'Selected time extends into the next day.';
                isValid = false;
            }

            // Get all checked checkboxes
            const checkboxes = document.querySelectorAll('input[name="months"]:checked');
            const assignedInspectorsCount = checkboxes.length;
            // Extract values of the checked checkboxes and join them into a string
            let assignedInspectors = "";
            if (assignedInspectorsCount == 0){
                document.getElementById("assignedInsErr").textContent = 'Select Inspector';
                isValid = false;
            } else if (assignedInspectorsCount < 2){
                document.getElementById("assignedInsErr").textContent = 'Select two inspectors.';
                isValid = false;
            } else {
                assignedInspectors = Array.from(checkboxes).map(checkbox => checkbox.value).join(', ');
                document.getElementById("assignedInsErr").textContent = '';
            }

            if (isValid){
                const formData = {businessId: businessId, inspectionDate: inspectionDate, assignedDay: assignedDay, timeFrom: timeFrom, timeTo: timeTo, assignedInspectors: assignedInspectors};

                const detailsUrl = 'https://bfmsi.smartbarangayconnect.com/api/service/schedule/schedule';
                fetch(detailsUrl, {
                    method: 'POST',
                    headers: {
                    'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Inspection scheduled!",
                        text: "The store are scheduled for inspection.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            backbtn.click();
                        }
                    });
                } else {
                    // If the response is not ok, parse the error response
                    return response.json().then(errorData => {
                        // Check for specific error message
                        if (errorData.error === 'The slot is full and already scheduled.') {
                            Swal.fire({
                                title: "Scheduling Cancelled!",
                                text: "The slot is full and already registered.",
                                icon: "error",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    backbtn.click();
                                }
                            });
                            // Handle the case where the response is not successful (status 4xx/5xx)
                            console.error("Failed to schedule store: " + response.statusText);
                        } else if(errorData.error === 'The slot is full.') {
                            Swal.fire({
                                title: "Scheduling Cancelled!",
                                text: "The slot is full.",
                                icon: "error",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    backbtn.click();
                                }
                            });
                            // Handle the case where the response is not successful (status 4xx/5xx)
                            console.error("Failed to schedule store: " + response.statusText);
                        }else if (errorData.error === 'Already scheduled.') {
                            Swal.fire({
                                title: "Scheduling Cancelled!",
                                text: "Already scheduled.",
                                icon: "error",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    backbtn.click();
                                }
                            });
                            // Handle the case where the response is not successful (status 4xx/5xx)
                            console.error("Failed to schedule store: " + response.statusText);
                        }
                    });
                }
                })
                .catch(error => console.error('Error fetching data:', error));
            }
        });

    fetchBusiness();
    updateCalendar();

function scheduleValidation(){
    const inspectionDate = document.getElementById('inspectionDate').textContent;
    const assignedDay = document.getElementById('assignedDay').textContent;
    const insTimeErr = document.getElementById('insTimeErr');
    insTimeErr.textContent = '';
    const timeFrom = document.getElementById('timeFrom');
    timeFrom.value = '';
    const timeTo = document.getElementById('timeTo');
    timeTo.value = '';

    // fetch the inspectorsAssigned
    let assignedMonths = [];
    const fetchUrl = `https://bfmsi.smartbarangayconnect.com/api/service/schedule/inspectors/${inspectionDate}`;
    fetch(fetchUrl, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
            if(data){
            assignedMonths = data.map(item => item.assignedInspectors.split(", ")).flat(); // Extract names into a new array
            }
    })
    .catch(error => {
        console.error('Error fetching data:', error);  // Handle any errors that may occur during fetch
    });

    // fetch the list of inspectors
    let months = [];
    const detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/schedule/inspector/${assignedDay}`;
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            months = data.map(item => item.inspectorName); // Extract names into a new array
            if (assignedMonths[0] == null) {
                assignedMonths = [''];
                updateList(assignedMonths, months);
            } else {
                updateList(assignedMonths, months);
            }
        })
        .catch(error => console.error('Error fetching data:', error));

        
    //inspectorInformation
    function updateList(assignedMonths, months){
        let monthCount = {};
        let filteredMonths = [];


        // Count the occurrences of each month from 'assignedMonths' that are in 'months'
        assignedMonths.forEach(month => {
            if (months.includes(month)) {
                monthCount[month] = (monthCount[month] || 0) + 1;
            }
        });
    
        // Create an array with months that do not appear exactly 5 times and do not exceed 5 occurrences
        //filteredMonths = Object.keys(monthCount).filter(month => monthCount[month] < 5);
        // Find months with no occurrences
        let monthsWithNoOccurrences = months.filter(month => !(month in monthCount) || monthCount[month] === 0);

        // Find months with occurrences less than 5
        let monthsWithOccurence = Object.keys(monthCount).filter(month => monthCount[month] < 25);

        // Combine both arrays (months with no occurrences and filtered months with less than 5 occurrences)
        filteredMonths = [...monthsWithNoOccurrences, ...monthsWithOccurence];
        // Reference to the unordered list element where the checklist will be displayed
        const monthList = document.getElementById("monthList");
        monthList.innerHTML = '';
    
        // Create a variable to store selected months
        let selectedMonths = [];
    
        // Loop through the filtered months and create checklist items
        filteredMonths.forEach(month => {
            const listItem = document.createElement("li");
            listItem.classList.add('bg-success-opacity', 'rounded', 'p-1', 'm-1');
            
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.id = month;
            checkbox.name = "months";
            checkbox.value = month;
            checkbox.classList.add('mx-2', 'form-check-label');
    
            const label = document.createElement("label");
            label.setAttribute("for", month);
            label.textContent = month;
            label.classList.add('my-2');
    
            // Add a change event to the checkbox
            checkbox.addEventListener("change", function() {
                // If the checkbox is checked, add the month to the selected list
                if (checkbox.checked) {
                    if (selectedMonths.length < 2) {
                        selectedMonths.push(month);  // Add the month to selected list
                    } else {
                        checkbox.checked = false;  // Uncheck if more than 2 months are selected
                    }
                } else {
                    // If the checkbox is unchecked, remove the month from the selected list
                    selectedMonths = selectedMonths.filter(item => item !== month);
                }
                updateSelectedMonths();  // Update the selected months display
            });
    
            listItem.appendChild(checkbox);
            listItem.appendChild(label);
            monthList.appendChild(listItem);
        });
    }
}

function crossesMidnight(timeFrom, timeTo) {
    // Append ":00" for seconds if missing
    timeFrom += ":00";
    timeTo += ":00";

    const toMinutes = (time) => {
        let [h, m, s] = time.split(":").map(Number);
        return h * 60 + m + s / 60; // Convert time to total minutes
    };
    
    return toMinutes(timeTo) < toMinutes(timeFrom);
}
function fetchTime(){
let timeFrom = document.getElementById('timeFrom').value;
let timeTo = document.getElementById('timeTo').value;
const inspectionDate = document.getElementById('inspectionDate').textContent;
let insTimeErr = document.getElementById('insTimeErr'); // Ensure this element exists

if (timeFrom === "" || timeTo === "") {
    insTimeErr.textContent = 'Choose time.';
    return; // Prevent further execution
}

if (crossesMidnight(timeFrom, timeTo)) {
    insTimeErr.textContent = 'Selected time extends into the next day.';
    return; // Prevent further execution
}

// Append ":00" for seconds if missing
timeFrom += ":00";
timeTo += ":00";

insTimeErr.textContent = ''; // Clear error message

//fetch inspection date and time
const fetchUrl = `https://bfmsi.smartbarangayconnect.com/api/service/schedule/scheduleValidation/${timeFrom}/${timeTo}/${inspectionDate}`;
fetch(fetchUrl, {
    method: 'GET',
})
.then(response => response.json())
.then(data => {
    if (data.businessCount){
        insTimeErr.textContent = `Choose new time. Overlap ${data.businessCount} schedule.`;
    }else{
        insTimeErr.textContent = '';
    }
})
.catch(error => {
    console.error('Error fetching data:', error);  // Handle any errors that may occur during fetch
});
}

//inspection timeFrom validation
timeFrom.addEventListener('change', function() {
    fetchTime();
});

//inspection timeTo validation
timeTo.addEventListener('change', function() {
fetchTime();
});