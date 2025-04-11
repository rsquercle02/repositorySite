
function fetchTable(){
    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    // Example URL for fetching detailed information (you may adjust it)
    const detailsUrl = 'http://localhost:8001/api/service/concernslist/sfetchReports';
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
                idCell.textContent = item.report_id;
                row.appendChild(idCell);

                const nameCell = document.createElement("td");
                nameCell.textContent = item.store_name;
                row.appendChild(nameCell);

                const addressCell = document.createElement("td");
                addressCell.textContent = item.store_address;
                row.appendChild(addressCell);

                const statusCell = document.createElement("td");
                statusCell.textContent = item.report_status;
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
                    fetchItemDetails(item.report_id);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    function fetchItemDetails(id) {

        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concernslist/sfetch/${id}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('reportId').textContent = data.report_id;
                document.getElementById('concernId').textContent = data.concern_id;
                document.getElementById('storeId').textContent = data.store_id;
                document.getElementById('storeName').textContent = data.store_name;
                document.getElementById('storeAddress').textContent = data.store_address;
                document.getElementById('reportDetails').textContent = data.report_details;
                document.getElementById('storeViolations').textContent = data.store_violations;
                document.getElementById('createAt').textContent = data.create_at;
                document.getElementById('reportStatus').textContent = data.report_status;
                document.getElementById('rstatusReason').textContent = data.rstatus_reason;
                document.getElementById("file1").innerHTML = '';
                document.getElementById("file2").innerHTML = '';
                document.getElementById("file3").innerHTML = '';
                document.getElementById('previousActions').textContent = data.actions;
                document.getElementById("afile1").innerHTML = '';
                document.getElementById("afile2").innerHTML = '';
                document.getElementById("afile3").innerHTML = '';

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
                displayFile(data.file2, "file2");
                displayFile(data.file3, "file3");
                displayFile(data.afile1, "afile1");
                displayFile(data.afile2, "afile2");
                displayFile(data.afile3, "afile3");
            })
            .catch(error => console.error('Error fetching details:', error));
    }

    // Search function
    function search(searchTerm){
        const tableBody = document.querySelector("#table tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concernslist/ssearch/${searchTerm}`;
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
                    idCell.textContent = item.report_id;
                    row.appendChild(idCell);
    
                    const nameCell = document.createElement("td");
                    nameCell.textContent = item.store_name;
                    row.appendChild(nameCell);
    
                    const addressCell = document.createElement("td");
                    addressCell.textContent = item.store_address;
                    row.appendChild(addressCell);
    
                    const statusCell = document.createElement("td");
                    statusCell.textContent = item.report_status;
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
                        fetchItemDetails(item.report_id);
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

    // Submit the report when the submit button is clicked
    document.getElementById('submitReport').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('reportTxtErr').innerHTML = '';
        document.getElementById('statusSelectErr').innerHTML = '';
        const concernId = document.getElementById('concernId').innerText;
        const reportId = document.getElementById('reportId').innerText;
        const storeId = document.getElementById('storeId').innerText;
        const reportText = document.getElementById('reportTextarea').value;
        const statusSelect = document.getElementById('statusSelect').value;
        const image1 = document.getElementById('image1').files[0];
        const image2 = document.getElementById('image2').files[0];
        const image3 = document.getElementById('image3').files[0];

    let isValid = true;

    if(reportText == ''){
        document.getElementById('reportTxtErr').innerHTML = 'Enter report.';
        isValid = false;
    }

    if(statusSelect == ''){
        document.getElementById('statusSelectErr').innerHTML = 'Enter status.';
        isValid = false;
    }

    if(!image1){
        document.getElementById('image1Err').innerHTML = 'Upload image1.';
        isValid = false;
    }

    if(!image2){
        document.getElementById('image2Err').innerHTML = 'Upload image2.';
        isValid = false;
    }

    if(!image3){
        document.getElementById('image3Err').innerHTML = 'Upload image3.';
        isValid = false;
    }

    if(isValid){

        // Create a FormData object to send the file
        const formData = new FormData();
        formData.append('reportId', reportId);
        formData.append('concernId', concernId);
        formData.append('storeId', storeId);
        formData.append('reportText', reportText);
        formData.append('statusSelect', statusSelect);
        formData.append('image1', image1);
        formData.append('image2', image2);
        formData.append('image3', image3);

        const detailsUrl = `http://localhost:8001/api/service/concernslist/spost`;
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
                        document.getElementById('reportId').textContent = '';
                        document.getElementById('concernId').textContent = '';
                        document.getElementById('storeId').textContent = '';
                        document.getElementById('storeName').textContent = '';
                        document.getElementById('storeAddress').textContent = '';
                        document.getElementById('reportDetails').textContent = '';
                        document.getElementById('storeViolations').textContent = '';
                        document.getElementById('createAt').textContent = '';
                        document.getElementById('reportStatus').textContent = '';
                        document.getElementById('rstatusReason').value = '';
                        document.getElementById('previousActions').value = '';
                        document.getElementById('reportTextarea').value = '';
                        document.getElementById('statusSelect').value = '';
                        fetchTable();
                        document.getElementById("actionReport").reset();
                        document.getElementById('closePreviewBtn').click();
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
                            document.getElementById('reportId').textContent = '';
                            document.getElementById('concernId').textContent = '';
                            document.getElementById('storeId').textContent = '';
                            document.getElementById('storeName').textContent = '';
                            document.getElementById('storeAddress').textContent = '';
                            document.getElementById('reportDetails').textContent = '';
                            document.getElementById('storeViolations').textContent = '';
                            document.getElementById('createAt').textContent = '';
                            document.getElementById('reportStatus').textContent = '';
                            document.getElementById('rstatusReason').value = '';
                            document.getElementById('previousActions').value = '';
                            document.getElementById('reportTextarea').value = '';
                            document.getElementById('statusSelect').value = '';
                            fetchTable();
                            document.getElementById("actionReport").reset();
                            document.getElementById('closePreviewBtn').click();                        }
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
                    document.getElementById('reportId').textContent = '';
                    document.getElementById('concernId').textContent = '';
                    document.getElementById('storeId').textContent = '';
                    document.getElementById('storeName').textContent = '';
                    document.getElementById('storeAddress').textContent = '';
                    document.getElementById('reportDetails').textContent = '';
                    document.getElementById('storeViolations').textContent = '';
                    document.getElementById('createAt').textContent = '';
                    document.getElementById('reportStatus').textContent = '';
                    document.getElementById('rstatusReason').value = '';
                    document.getElementById('previousActions').value = '';
                    document.getElementById('reportTextarea').value = '';
                    document.getElementById('statusSelect').value = '';
                    fetchTable();
                    document.getElementById("actionReport").reset();
                    document.getElementById('closePreviewBtn').click();
                }
            });
        });
    }
    });

    fetchTable();