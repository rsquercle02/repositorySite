
function fetchTable(){
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';
    document.querySelector('.tablehtml').style.display = 'none';

    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    // Example URL for fetching detailed information (you may adjust it)
    const detailsUrl = 'http://localhost:8001/api/service/concerns/fetchConcerns';
    // Fetch data from the API endpoint to populate the table
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            if(data){
                insights(data);
            }
            const tableBody = document.querySelector("#table tbody");
            tableBody.innerHTML = ''; //This will remove all the previous rows
            data.forEach(item => {
                const row = document.createElement("tr");

                // Create table data cells dynamically
                const idCell = document.createElement("td");
                idCell.textContent = item.concern_id;
                row.appendChild(idCell);

                const nameCell = document.createElement("td");
                nameCell.textContent = item.store_name;
                row.appendChild(nameCell);

                const addressCell = document.createElement("td");
                addressCell.textContent = item.store_address;
                row.appendChild(addressCell);

                const residenttypeCell = document.createElement("td");
                residenttypeCell.textContent = item.resident_type;
                row.appendChild(residenttypeCell);

                const statusCell = document.createElement("td");
                statusCell.textContent = item.anonymity_status;
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
                button.setAttribute("data-bs-target", "#previewModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.concern_id);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error))
        .finally(() => {
            // Hide loading
            document.getElementById('loading-indicator').style.display = 'none';
            document.querySelector('.tablehtml').style.display = 'block';
        });
    }

    function fetchItemDetails(id) {

        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concerns/fetchDetails/${id}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('concernId').textContent = data.concern_id;
                if(data.anonymity_status == 'non-anonymous'){
                    document.getElementById('concernedCitizen').textContent = data.fullname;
                } else if(data.anonymity_status == 'anonymous'){
                    document.getElementById('concernedCitizen').textContent = "Anonymous";
                }
                document.getElementById('storeId').textContent = data.store_id;
                document.getElementById('storeName').textContent = data.store_name;
                document.getElementById('storeAddress').textContent = data.store_address;
                document.getElementById('storeRecord').textContent = data.record_status;
                document.getElementById('concernDetails').textContent = data.concern_details;
                document.getElementById('concernStatus').textContent = data.concern_status;
                document.getElementById('createAt').textContent = data.create_at;
                document.getElementById("file1").innerHTML = '';

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
                displayFile(data.file1, "file1");
                
            })
            .catch(error => console.error('Error fetching details:', error));
    }

    // Search function
    function search(searchTerm){
        const tableBody = document.querySelector("#table tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concerns/searchBusiness/${searchTerm}`;
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
                    idCell.textContent = item.concern_id;
                    row.appendChild(idCell);
    
                    const nameCell = document.createElement("td");
                    nameCell.textContent = item.store_name;
                    row.appendChild(nameCell);
    
                    const addressCell = document.createElement("td");
                    addressCell.textContent = item.store_address;
                    row.appendChild(addressCell);
    
                    const residenttypeCell = document.createElement("td");
                    residenttypeCell.textContent = item.resident_type;
                    row.appendChild(residenttypeCell);

                    const statusCell = document.createElement("td");
                    statusCell.textContent = item.anonymity_status;
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
                    button.setAttribute("data-bs-target", "#previewModal");
    
                    // Add event listener to the button
                    button.addEventListener('click', function() {
                        fetchItemDetails(item.concern_id);
                    });
    
                    actionCell.appendChild(button);
                    row.appendChild(actionCell);
    
                    // Append the row to the table body
                    tableBody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching data:', error));
        }

    document.getElementById("searchTerm").addEventListener('input', function(event) {
        const searchTerm = document.getElementById("searchTerm").value.trim();
        if (searchTerm === '') {
            // If the search box is empty, display all the data
            fetchTable(); // `data` is your full data array
        } else {
            // Otherwise, update the table with filtered results based on the search term
            search(searchTerm);
        }
    });

    function notValid(){
        Swal.fire({
            title: "Update status?",
            text: "",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, update."
            }).then((result) => {
                if (result.isConfirmed) {
                    const concernId = document.getElementById("concernId").textContent;
                    const concernStatus = "Not valid.";
                    const formData = new FormData();
                    formData.append("concernStatus", concernStatus);
                    formData.append("concernId", concernId);

                    const detailsUrl = `http://localhost:8001/api/service/concerns/notValid`;
                        fetch(detailsUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            // Check if the response is okay (status 200-299)
                        if (response.ok) {
                            Swal.fire({
                                title: "Update status",
                                text: "The status is updated.",
                                icon: "success",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Remove data of the form
                                    document.getElementById('concernId').textContent = '';
                                    document.getElementById('concernedCitizen').textContent = '';
                                    document.getElementById('storeId').textContent = '';
                                    document.getElementById('storeName').textContent = '';
                                    document.getElementById('storeAddress').textContent = '';
                                    document.getElementById('storeRecord').textContent = '';
                                    document.getElementById('concernDetails').textContent = '';
                                    document.getElementById('createAt').textContent = '';
                                    document.getElementById('reportTextarea').value = '';
                                    document.getElementById('categorySelect').value = '';
                                    document.getElementById('violationSelect').value = '';
                                    //fetchTable();
                                    document.getElementById('closePreviewBtn').click();
                                    document.getElementById('cancelReport').click();
                                    clearAllViolations();


                                }
                            });
                        } else {
                        // If the response is not ok, parse the error response
                        return response.json().then(errorData => {
                                Swal.fire({
                                    title: "Update Cancelled!",
                                    text: `Error: ${errorData.error}`,
                                    icon: "error",
                                    confirmButtonColor: "#0f0"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Remove data of the form
                                        document.getElementById('concernId').textContent = '';
                                        document.getElementById('concernedCitizen').textContent = '';
                                        document.getElementById('storeId').textContent = '';
                                        document.getElementById('storeName').textContent = '';
                                        document.getElementById('storeAddress').textContent = '';
                                        document.getElementById('storeRecord').textContent = '';
                                        document.getElementById('concernDetails').textContent = '';
                                        document.getElementById('createAt').textContent = '';
                                        document.getElementById('reportTextarea').value = '';
                                        document.getElementById('categorySelect').value = '';
                                        document.getElementById('violationSelect').value = '';
                                        //fetchTable();
                                        document.getElementById('closePreviewBtn').click();  
                                        document.getElementById('cancelReport').click();
                                        clearAllViolations();                      }
                                });
                        });
                    }
                    })
                    .catch(error => {
                        // Check for specific error message
                        Swal.fire({
                            title: "Update Cancelled!",
                            text: `Error: ${error}`,
                            icon: "error",
                            confirmButtonColor: "#0f0"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Remove data of the form
                                document.getElementById('concernId').textContent = '';
                                document.getElementById('concernedCitizen').textContent = '';
                                document.getElementById('storeId').textContent = '';
                                document.getElementById('storeName').textContent = '';
                                document.getElementById('storeAddress').textContent = '';
                                document.getElementById('storeRecord').textContent = '';
                                document.getElementById('concernDetails').textContent = '';
                                document.getElementById('createAt').textContent = '';
                                document.getElementById('reportTextarea').value = '';
                                document.getElementById('categorySelect').value = '';
                                document.getElementById('violationSelect').value = '';
                                //fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('cancelReport').click();
                                clearAllViolations();
                            }
                        });
                    });
                }
            });
    }

    document.getElementById('notValidBtn').addEventListener('click', function() {
        notValid();
    });
    // Toggle visibility of the report section and hide "Create Report" button when the "Create Report" button is clicked
    document.getElementById('createReportBtn').addEventListener('click', function() {
        var reportSection = document.querySelector('.report-section');
        var createReportBtn = document.getElementById('createReportBtn');
        
        // Toggle visibility of the report section
        if (reportSection.style.display === 'none' || reportSection.style.display === '') {
            reportSection.style.display = 'block';
            createReportBtn.style.display = 'none'; // Hide the "Create Report" button
        } else {
            reportSection.style.display = 'none';
            createReportBtn.style.display = 'block'; // Show the "Create Report" button again
        }
    });

    // Cancel and hide the report section, and show the "Create Report" button
    document.getElementById('cancelReport').addEventListener('click', function() {
        var reportSection = document.querySelector('.report-section');
        var createReportBtn = document.getElementById('createReportBtn');
        reportSection.style.display = 'none'; // Hide the report section
        createReportBtn.style.display = 'block'; // Show the "Create Report" button
    });

    // Preview modal event listener
    document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
        // Remove data of the form
        document.getElementById('concernId').textContent = '';
        document.getElementById('storeId').textContent = '';
        document.getElementById('storeName').textContent = '';
        document.getElementById('storeAddress').textContent = '';
        document.getElementById('concernDetails').textContent = '';
        document.getElementById('createAt').textContent = '';
        document.getElementById('reportTextarea').value = '';
        document.getElementById('categorySelect').value = '';
        document.getElementById('violationSelect').value = '';
        fetchTable();
        document.getElementById('closePreviewBtn').click();
        document.getElementById('cancelReport').click();
        clearAllViolations();
    });


    // Submit the report when the submit button is clicked
    document.getElementById('submitReport').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('reportTxtErr').innerHTML = '';
        document.getElementById('categorySelectErr').innerHTML = '';
        document.getElementById('violationSelectErr').innerHTML = '';
        const concernId = document.getElementById('concernId').innerText;
        const storeId = document.getElementById('storeId').innerText;
        const reportText = document.getElementById('reportTextarea').value;
        const categorySelect = document.getElementById('categorySelect').value;
        const violationSelect = document.getElementById('violationSelect').value;

    let isValid = true;

    if(reportText == ''){
        document.getElementById('reportTxtErr').innerHTML = 'Enter report.';
        isValid = false;
    }

    if(violationSelect == ''){
        document.getElementById('violationSelectErr').innerHTML = 'Enter violation.';
        isValid = false;
    }

    if(categorySelect == ''){
        document.getElementById('categorySelectErr').innerHTML = 'Select category.';
        isValid = false;
    }

    if(isValid){
        
        // Create a FormData object to send the file
        const formData = new FormData();
        formData.append('concernId', concernId);
        formData.append('storeId', storeId);
        formData.append('reportText', reportText);
        formData.append('categorySelect', categorySelect);
        formData.append('violationSelect', violationSelect);

        const detailsUrl = `http://localhost:8001/api/service/concerns/postreport`;
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
                        document.getElementById('concernId').textContent = '';
                        document.getElementById('concernedCitizen').textContent = '';
                        document.getElementById('storeId').textContent = '';
                        document.getElementById('storeName').textContent = '';
                        document.getElementById('storeAddress').textContent = '';
                        document.getElementById('storeRecord').textContent = '';
                        document.getElementById('concernDetails').textContent = '';
                        document.getElementById('createAt').textContent = '';
                        document.getElementById('reportTextarea').value = '';
                        document.getElementById('categorySelect').value = '';
                        document.getElementById('violationSelect').value = '';
                        //fetchTable();
                        document.getElementById('closePreviewBtn').click();
                        document.getElementById('cancelReport').click();
                        clearAllViolations();


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
                            document.getElementById('concernId').textContent = '';
                            document.getElementById('concernedCitizen').textContent = '';
                            document.getElementById('storeId').textContent = '';
                            document.getElementById('storeName').textContent = '';
                            document.getElementById('storeAddress').textContent = '';
                            document.getElementById('storeRecord').textContent = '';
                            document.getElementById('concernDetails').textContent = '';
                            document.getElementById('createAt').textContent = '';
                            document.getElementById('reportTextarea').value = '';
                            document.getElementById('categorySelect').value = '';
                            document.getElementById('violationSelect').value = '';
                            //fetchTable();
                            document.getElementById('closePreviewBtn').click();  
                            document.getElementById('cancelReport').click();
                            clearAllViolations();                      }
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
                    document.getElementById('concernId').textContent = '';
                    document.getElementById('concernedCitizen').textContent = '';
                    document.getElementById('storeId').textContent = '';
                    document.getElementById('storeName').textContent = '';
                    document.getElementById('storeAddress').textContent = '';
                    document.getElementById('storeRecord').textContent = '';
                    document.getElementById('concernDetails').textContent = '';
                    document.getElementById('createAt').textContent = '';
                    document.getElementById('reportTextarea').value = '';
                    document.getElementById('categorySelect').value = '';
                    document.getElementById('violationSelect').value = '';
                    //fetchTable();
                    document.getElementById('closePreviewBtn').click();
                    document.getElementById('cancelReport').click();
                    clearAllViolations();
                }
            });
        });
    }
    });

    /* Violation list */
    // Get all violation button elements and the input field to display selected violations
    const violationOptions = document.querySelectorAll('.violation-option');
    const violationSelectField = document.getElementById('violationSelect');
    
    // Array to store selected violation values
    let violationSelect = [];
    
    // Function to update the selected violations field
    function updateviolationSelect() {
        // Join the selected violations array into a comma-separated string
        violationSelectField.value = violationSelect.join(', ').replace(/_/g, ' ').toUpperCase();
    }

    // Event listener for each violation option
    violationOptions.forEach(option => {
        option.addEventListener('click', function () {
            const violationValue = this.getAttribute('data-value');
            
            // Check if the violation is already selected
            if (violationSelect.includes(violationValue)) {
                // Deselect the violation
                violationSelect = violationSelect.filter(v => v !== violationValue);
                this.classList.remove('selected');  // Remove selected styling
            } else {
                // Add the violation to the selected violations array
                violationSelect.push(violationValue);
                this.classList.add('selected');  // Add selected styling
            }

            // Update the display field with the selected violations
            updateviolationSelect();
        });
    });

    function clearAllViolations() {
        // Clear the array of selected violations
        violationSelect = [];
    
        // Remove the "selected" class from all violation options
        violationOptions.forEach(option => {
            option.classList.remove('selected');
        });
    
        // Update the display field with the selected violations (which will be empty now)
        updateviolationSelect();
    }
    
    /*************** Report reminder *************/

    // AI summary and suggestions for inspection data
    function insights(reportList){
        // Get the current date in the format YYYY-MM-DD
        const currentDate = new Date().toISOString().split('T')[0]; // Format: "YYYY-MM-DD"
    
        // Create the promptData with the current date and the passed reportList
        const promptData = {
            prompt: "Can you create a simple reminder in sentence type about the concerns list? Can you say the concern id on list that needs more attention, can you prioritized the reports from older dates and resident type is resident?",
            report_list:Array.isArray(reportList) && reportList.length > 0 ? reportList : ["No reports"],  // Use the passed reportList here
            date_today: currentDate  // Use the current date
        }
    
        fetch('http://localhost:8001/api/service/ai/generatereminders', {
            method: 'POST',  // HTTP method
            headers: {
                'Content-Type': 'application/json'  // Set content type to JSON
            },
            body: JSON.stringify(promptData)  // Convert the prompt data to JSON format
        })
        .then(response => response.json())  // Parse the response as JSON
        .then(data => {
            // Instead of appending the result to the output div, show it in a SweetAlert
            showInSwal(data, currentDate);  // Pass current date to showInSwal
        }) // Handle the response
        .catch(error => {
            console.error('Error:', error);
        })  // Handle errors
        .finally(() => {
            // Hide loading
            //document.getElementById('loading-indicator').style.display = 'none';
        });
    }
    
    // Function to show the result inside SweetAlert
    function showInSwal(data, currentDate) {
        let content = '';  // Initialize content variable to hold the rendered HTML
    
        // Render the content using findAndRenderText
        findAndRenderText(data, (renderedText) => {
            content = renderedText;
    
            // Show the content inside a SweetAlert2 modal
            Swal.fire({
                position: "top-end",
                title: 'AI Insights',  // Popup title
                html: `  
                    <p>${content}</p>
                    <p><strong>Date Generated:</strong> ${currentDate}</p>  <!-- Add the current date in the Swal -->
                `,
                width: '80%',  // Default width (80% of the screen width)
                padding: '20px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-popup'  // Custom class for the popup to style it
                },
                didOpen: () => {
                    // Add custom styles dynamically on open (optional for responsiveness)
                    const popup = Swal.getPopup();
                    popup.style.maxWidth = '500px'; // Max width to prevent too large popups on big screens
                    popup.style.width = '80%'; // Ensure it stays responsive
                    popup.style.borderRadius = '10px'; // Rounded corners for a cleaner look
                }
            });
    
            // Add custom CSS for responsiveness
            const style = document.createElement('style');
            style.innerHTML = `
                .swal-popup {
                    max-width: 500px;  /* Limits the max width for larger screens */
                    width: 80%;  /* Default to 80% width */
                    border-radius: 10px;  /* For a smoother rectangular look */
                }
                @media (max-width: 768px) {
                    .swal-popup {
                        width: 90% !important; /* 90% width on mobile devices */
                    }
                }
                @media (max-width: 480px) {
                    .swal-popup {
                        width: 95% !important; /* Even wider for small screens */
                    }
                }
            `;
            document.head.appendChild(style);
    
                });
            }
    
            // Function to find and render text recursively
            function findAndRenderText(obj, callback) {
                let result = '';  // Initialize an empty string for the result
    
                if (typeof obj === 'object' && obj !== null) {
                    for (let key in obj) {
                        if (key === "text" && typeof obj[key] === "string") {
                            const cleaned = obj[key]
                                .replace(/\\n/g, '\n')
                                .replace(/\\"/g, '"')
                                .replace(/\\\\/g, '\\');
    
                            // Use marked.js to render the markdown into HTML
                            const html = marked.parse(cleaned);
                            result += html;  // Append the rendered HTML to the result string
                        } else {
                            result += findAndRenderText(obj[key], callback);  // Recurse for nested objects
                        }
                    }
                }
    
                // If a callback is provided, pass the result
                if (callback) {
                    callback(result);
                }
    
                return result;  // Return the final rendered text
            }

document.addEventListener('DOMContentLoaded', () => {
    fetchTable();
});