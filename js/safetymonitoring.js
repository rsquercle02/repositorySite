let currentcategory = 'pending';
let searchinput = '';

//event listeners
document.getElementById('reportStatus').addEventListener('change', function(){
    const reportStatus = document.getElementById('reportStatus').value;
    if(reportStatus == 'showPending'){
    currentcategory = 'pending';
    fetchTable(currentcategory, searchinput);
    } else if(reportStatus == 'showSent'){
        currentcategory = 'sent';
        fetchTable(currentcategory, searchinput);
    }
});

document.getElementById('searchTerm').addEventListener('keyup', function(){
    searchinput = document.getElementById('searchTerm').value;
   if (currentcategory == 'pending'){
    fetchTable(currentcategory, searchinput);
   } else if (currentcategory == 'sent'){
    fetchTable(currentcategory, searchinput);
   }
});

document.getElementById('clrupdateStatus').addEventListener('click', function(){
    updateStatus();
});

function fetchTable(currentcategory, searchinput){
    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows

    let detailsUrl = null;
    if(searchinput == ''){
        if(currentcategory == 'pending'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsPending`;
        } else if(currentcategory == 'sent'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsSent`;
        }
    }else {
        if(currentcategory == 'pending'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsPending?search=${searchinput}`;
        } else if(currentcategory == 'sent'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsSent?search=${searchinput}`;
        }
    }

    // Fetch data from the API endpoint to populate the table
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#table tbody");
            tableBody.innerHTML = ''; //This will remove all the previous rows
            data.forEach(item => {
                const row = document.createElement("tr");

                // Create table data cells dynamically
                const idCell = document.createElement("td");
                idCell.textContent = item.clrngops_id;
                row.appendChild(idCell);

                const titleCell = document.createElement("td");
                titleCell.textContent = item.title;
                row.appendChild(titleCell);

                const dateCell = document.createElement("td");
                dateCell.textContent = item.date;
                row.appendChild(dateCell);

                const statusCell = document.createElement("td");
                statusCell.textContent = item.clrngops_status;
                row.appendChild(statusCell);

                const createdAtCell = document.createElement("td");
                createdAtCell.textContent = item.create_at;
                row.appendChild(createdAtCell);

                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "View";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#reportPreview");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(currentcategory, item.clrngops_id);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

function fetchItemDetails(currentcategory, id) {
    console.log('Fetch function');

    // Example URL for fetching detailed information (you may adjust it)
    const detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsFetch/${id}`;

    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            //document.getElementById("previewId").innerText = data.id;
            // Populate HTML with dynamic data
            console.log(data.clrngops_id);
            console.log(data.title);
            document.getElementById('clrreportId').textContent = data.clrngops_id;
            document.getElementById('clrreportTitle').textContent = data.title;
            document.getElementById('clrreportDate').textContent = data.date;
            document.getElementById('clrreportTime').textContent = data.time;
            document.getElementById('clrreportStaff').textContent = data.staff;
            document.getElementById('clrreportDetails').textContent = data.details;
            document.getElementById("clrbeforeFile1").innerHTML = '';
            document.getElementById("clrbeforeFile2").innerHTML = '';
            document.getElementById("clrbeforeFile3").innerHTML = '';
            document.getElementById("clrafterFile1").innerHTML = '';
            document.getElementById("clrafterFile2").innerHTML = '';
            document.getElementById("clrafterFile3").innerHTML = '';
            if(currentcategory == 'pending'){
            document.getElementById("clrupdateStatus").style.display = 'block';
            } else if(currentcategory == 'sent'){
            document.getElementById("clrupdateStatus").style.display = 'none';
            }

            // Function to extract file extension from URL
            function getFileExtension(url) {
                var fileName = url.split('/').pop();  // Get the last part of the URL (file name)
                var fileExtension = fileName.split('.').pop().toLowerCase();  // Get the file extension
                return fileExtension;
            }

            // Function to display a file based on file extension
            function displayFile(fileUrl, id) {
                var fileExtension = getFileExtension(fileUrl);
                var fileContainer = document.getElementById(id);
                
                if (fileExtension === 'jpeg' || fileExtension === 'jpg' || fileExtension === 'png') {
                    // If the file is an image (JPEG, JPG, or PNG), show an <img> element
                    var img = document.createElement('img');
                    img.src = fileUrl;  // Set the source to the file URL
                    img.alt = "Image File";
                    img.style.maxWidth = '100%';
                    fileContainer.appendChild(img);
                }else {
                    // If the file type is not supported, show a message
                    var unsupportedMessage = document.createElement('p');
                    unsupportedMessage.textContent = `Unsupported file type: ${fileUrl}`;
                    fileContainer.appendChild(unsupportedMessage);
                }
            }

            // Call the function for each file individually
            displayFile(data.before_file1, "clrbeforeFile1");
            displayFile(data.before_file2, "clrbeforeFile2");
            displayFile(data.before_file3, "clrbeforeFile3");
            displayFile(data.after_file1, "clrafterFile1");
            displayFile(data.after_file2, "clrafterFile2");
            displayFile(data.after_file3, "clrafterFile3");
        })
        .catch(error => console.error('Error fetching details:', error));
}

function updateStatus(){
    const clrreportId = document.getElementById("clrreportId").innerText;
    $status = 'Sent.';
    // Create a FormData object to send the file
    const formData = new FormData();
    formData.append('clrreportId', clrreportId);
    formData.append('status', $status);

    const detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsUpdate`;
        fetch(detailsUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if the response is okay (status 200-299)
        if (response.ok) {
            Swal.fire({
                title: "Create Report",
                text: "The report is created.",
                icon: "success",
                confirmButtonColor: "#0f0"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove data of the form
                    //fetchTable();
                    document.getElementById("reportForm").reset();
                    document.getElementById('closereportBtn').click();
                }
            });
        } else {
        // If the response is not ok, parse the error response
        return response.json().then(errorData => {
                Swal.fire({
                    title: "Upload Cancelled!",
                    text: `Error: ${errorData.error}`,
                    icon: "error",
                    confirmButtonColor: "#0f0"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Remove data of the form
                        //fetchTable();
                        document.getElementById("reportForm").reset();
                        document.getElementById("closereportBtn").click();
                    }
                });
        });
        }
    })
    .catch(error => {
        // Check for specific error message
        Swal.fire({
            title: "Upload Cancelled!",
            text: `Error: ${error}`,
            icon: "error",
            confirmButtonColor: "#0f0"
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove data of the form
                //fetchTable();
                document.getElementById("reportForm").reset();
                document.getElementById('reportBtn').click();
            }
        });
    });
}


/* Staff select */
// Get the input field to display selected staff
const staffSelectField = document.getElementById('selectedstaff');  // Updated ID to match the HTML

// Array to store staff names
const staffNames = ["Kagawad 1", "Kagawad 2", "Kagawad 3", "Secretary", "Captain"];  // Add more names as needed

// Array to store selected staff values
let staffSelect = [];

// Function to update the selected staff field
function updateStaffSelect() {
    // Join the selected staff array into a comma-separated string
    staffSelectField.value = staffSelect.join(', '); // No need for replace() as it's no longer using underscores
}

// Function to generate staff buttons dynamically
function generateStaffButtons() {
    const staffButtonsContainer = document.getElementById('staffButtonsContainer');
    
    // Clear any existing buttons in the container (optional)
    staffButtonsContainer.innerHTML = '';

    // Loop through staff names and create buttons
    staffNames.forEach(staffName => {
        const button = document.createElement('button');
        button.classList.add('staff-option', 'btn', 'btn-primary'); // Bootstrap button classes
        button.setAttribute('type', 'button');  // Set button type
        button.setAttribute('data-value', staffName);  // Add a data-value attribute with a sanitized name
        button.textContent = staffName;

        // Add event listener for the button click
        button.addEventListener('click', function () {
            const staffValue = this.getAttribute('data-value');
            
            // Check if the staff is already selected
            if (staffSelect.includes(staffValue)) {
                // Deselect the staff
                staffSelect = staffSelect.filter(s => s !== staffValue);
                this.classList.remove('selected');  // Remove selected styling
            } else {
                // Add the staff to the selected staff array
                staffSelect.push(staffValue);
                this.classList.add('selected');  // Add selected styling
            }

            // Update the display field with the selected staff
            updateStaffSelect();
        });

        // Append the button to the container
        staffButtonsContainer.appendChild(button);
    });
}

// Function to clear all selected staff
function clearAllStaff() {
    // Clear the array of selected staff
    staffSelect = [];

    // Remove the "selected" class from all staff options
    const allStaffButtons = document.querySelectorAll('.staff-option');
    allStaffButtons.forEach(button => {
        button.classList.remove('selected');
    });

    // Update the display field with the selected staff (which will be empty now)
    updateStaffSelect();
}

document.getElementById('createReport').addEventListener('click', function(){
    generateStaffButtons();
});

// Initialize the staff buttons on page load
//generateStaffButtons();

//Captain submit report
document.getElementById('submitButton').addEventListener('click', function(event) {
    event.preventDefault();
    document.getElementById('titleErr').innerHTML = '';
    document.getElementById('dateErr').innerHTML = '';
    document.getElementById('timeErr').innerHTML = '';
    document.getElementById('staffselectErr').innerHTML = '';
    document.getElementById('detailsErr').innerHTML = '';
    document.getElementById('beforeimage1Err').innerHTML = '';
    document.getElementById('beforeimage2Err').innerHTML = '';
    document.getElementById('beforeimage3Err').innerHTML = '';
    document.getElementById('afterimage1Err').innerHTML = '';
    document.getElementById('afterimage2Err').innerHTML = '';
    document.getElementById('afterimage3Err').innerHTML = '';

    const title = document.getElementById('title').value;
    const date = document.getElementById('date').value;
    const time = document.getElementById('time').value;
    const selectedstaff = document.getElementById('selectedstaff').value;
    const details = document.getElementById('details').value;
    const beforeimage1 = document.getElementById('beforeimage1').files[0];
    const beforeimage2 = document.getElementById('beforeimage2').files[0];
    const beforeimage3 = document.getElementById('beforeimage3').files[0];
    const afterimage1 = document.getElementById('afterimage1').files[0];
    const afterimage2 = document.getElementById('afterimage2').files[0];
    const afterimage3 = document.getElementById('afterimage3').files[0];

let isValid = true;

if(title == ''){
    document.getElementById('titleErr').innerHTML = 'Enter title.';
    isValid = false;
}

if(date == ''){
    document.getElementById('dateErr').innerHTML = 'Enter date.';
    isValid = false;
}

if(time == ''){
    document.getElementById('timeErr').innerHTML = 'Enter time.';
    isValid = false;
}

if(selectedstaff == ''){
    document.getElementById('staffselectErr').innerHTML = 'Select staff.';
    isValid = false;
}

if(details == ''){
    document.getElementById('detailsErr').innerHTML = 'Enter details.';
    isValid = false;
}

if(!beforeimage1){
    document.getElementById('beforeimage1Err').innerHTML = 'Upload image1.';
    isValid = false;
}

if(!beforeimage2){
    document.getElementById('beforeimage2Err').innerHTML = 'Upload image2.';
    isValid = false;
}

if(!beforeimage3){
    document.getElementById('beforeimage3Err').innerHTML = 'Upload image3.';
    isValid = false;
}

if(!afterimage1){
    document.getElementById('afterimage1Err').innerHTML = 'Upload image1.';
    isValid = false;
}

if(!afterimage2){
    document.getElementById('afterimage2Err').innerHTML = 'Upload image2.';
    isValid = false;
}

if(!afterimage3){
    document.getElementById('afterimage3Err').innerHTML = 'Upload image3.';
    isValid = false;
}

if(isValid){

    // Create a FormData object to send the file
    const formData = new FormData();
    formData.append('title', title);
    formData.append('date', date);
    formData.append('time', time);
    formData.append('staff', selectedstaff);
    formData.append('details', details);
    formData.append('beforeimage1', beforeimage1);
    formData.append('beforeimage2', beforeimage2);
    formData.append('beforeimage3', beforeimage3);
    formData.append('afterimage1', afterimage1);
    formData.append('afterimage2', afterimage2);
    formData.append('afterimage3', afterimage3);

    const detailsUrl = `http://localhost:8001/api/service/concernslist/clrngopsreport`;
        fetch(detailsUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if the response is okay (status 200-299)
        if (response.ok) {
            Swal.fire({
                title: "Create Report",
                text: "The report is created.",
                icon: "success",
                confirmButtonColor: "#0f0"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Remove data of the form
                    //fetchTable();
                    document.getElementById("reportForm").reset();
                    document.getElementById('closereportBtn').click();
                }
            });
        } else {
        // If the response is not ok, parse the error response
        return response.json().then(errorData => {
                Swal.fire({
                    title: "Upload Cancelled!",
                    text: `Error: ${errorData.error}`,
                    icon: "error",
                    confirmButtonColor: "#0f0"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Remove data of the form
                        //fetchTable();
                        document.getElementById("reportForm").reset();
                        document.getElementById("closereportBtn").click();
                    }
                });
        });
        }
    })
    .catch(error => {
        // Check for specific error message
        Swal.fire({
            title: "Upload Cancelled!",
            text: `Error: ${error}`,
            icon: "error",
            confirmButtonColor: "#0f0"
        }).then((result) => {
            if (result.isConfirmed) {
                // Remove data of the form
                //fetchTable();
                document.getElementById("reportForm").reset();
                document.getElementById('reportBtn').click();
            }
        });
    });
}
});

fetchTable(currentcategory, searchinput);