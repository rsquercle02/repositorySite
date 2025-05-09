let currentcategory = 'category1';
let filter = null;
let searchinput = '';
//event listeners
document.getElementById('categorySelect').addEventListener('change', function(){
    const categorySelect = document.getElementById('categorySelect').value;
    if(categorySelect == 'all'){
    currentcategory = 'category1';
    fetchTable(currentcategory, searchinput);
    } else if(categorySelect == 'lowrisk'){
        currentcategory = 'category2';
        fetchTable(currentcategory, searchinput);
    } else if(categorySelect == 'mediumrisk'){
        currentcategory = 'category3';
        fetchTable(currentcategory, searchinput);
    } else if(categorySelect == 'highrisk'){
        currentcategory = 'category4';
        fetchTable(currentcategory, searchinput);
    } else if(categorySelect == 'resolved'){
        currentcategory = 'category5';
        fetchTable(currentcategory, searchinput);
    }
});

document.getElementById('searchTerm').addEventListener('keyup', function(){
    searchinput = document.getElementById('searchTerm').value;
   if (currentcategory == 'category1'){
    fetchTable(currentcategory, searchinput);
   } else if (currentcategory == 'category2'){
    fetchTable(currentcategory, searchinput);
   } else if (currentcategory == 'category3'){
    fetchTable(currentcategory, searchinput);
   } else if (currentcategory == 'category4'){
    fetchTable(currentcategory, searchinput);
   } else if (currentcategory == 'category5'){
    fetchTable(currentcategory, searchinput);
   }
});

function fetchTable(currentcategory, searchinput){
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    let detailsUrl = null;
    if(searchinput == ''){
        if(currentcategory == 'category1'){
            detailsUrl = `http://localhost:8001/api/service/reports/allReports`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/reports/lowriskReports`;
        } else if(currentcategory == 'category3'){
            detailsUrl = 'http://localhost:8001/api/service/reports/mediumriskReports';
        } else if(currentcategory == 'category4'){
            detailsUrl = 'http://localhost:8001/api/service/reports/highriskReports';
        } else if(currentcategory == 'category5'){
            detailsUrl = 'http://localhost:8001/api/service/reports/resolvedReports';
        }
    } else{
        if(currentcategory == 'category1'){
            detailsUrl = `http://localhost:8001/api/service/reports/allReports?search=${searchinput}`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/reports/lowriskReports?search=${searchinput}`;
        } else if(currentcategory == 'category3'){
            detailsUrl = `http://localhost:8001/api/service/reports/mediumriskReports?search=${searchinput}`;
        } else if(currentcategory == 'category4'){
            detailsUrl = `http://localhost:8001/api/service/reports/highriskReports?search=${searchinput}`;
        } else if(currentcategory == 'category5'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedReports?search=${searchinput}`;
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

                if(currentcategory == 'category1' || currentcategory == 'category2'
                    || currentcategory == 'category3' || currentcategory == 'category4'){
                const createdAtCell = document.createElement("td");
                createdAtCell.textContent = item.create_at;
                row.appendChild(createdAtCell);
                } else if(currentcategory == 'category5'){
                const createdAtCell = document.createElement("td");
                createdAtCell.textContent = item.update_at;
                row.appendChild(createdAtCell);
                }

                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "View";
                button.classList.add("btn", "btn-success", "mr-2");
                button.setAttribute("data-bs-toggle", "modal");

                if(currentcategory == 'category1' || currentcategory == 'category2' || 
                    currentcategory == 'category3' || currentcategory == 'category4'){
                    button.setAttribute("data-bs-target", "#kpreviewModal");
                } else if(currentcategory == 'category5'){
                    button.setAttribute("data-bs-target", "#cpreviewModal");
                }

                /****** Print report **********/
                // Create the button and add it to the last column
                const printbutton = document.createElement("button");
                printbutton.textContent = "Print";
                printbutton.classList.add("btn", "btn-success");

                if(currentcategory == 'category1' || currentcategory == 'category2'
                    || currentcategory == 'category3' || currentcategory == 'category4'){
                // Add event listener to the button
                button.addEventListener('click', function() {
                    kfetchItemDetails(item.report_id);
                });

                // Add event listener to the button
                printbutton.addEventListener('click', function() {
                    updateReportContent(item.report_id);
                    openReportModal();
                });

                }  else if(currentcategory == 'category5'){

                // Add event listener to the button
                button.addEventListener('click', function() {
                    kfetchItemDetails(item.action_id);
                });

                // Add event listener to the button
                printbutton.addEventListener('click', function() {
                    updateActionContent(item.action_id);
                    openActionModal();
                });

                }

                actionCell.appendChild(button);
                actionCell.appendChild(printbutton);
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

    function kfetchItemDetails(id) {
        if(currentcategory == 'category1'){
            detailsUrl = `http://localhost:8001/api/service/reports/allfetch/${id}`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/reports/lowriskfetch/${id}`;
        } else if(currentcategory == 'category3'){
            detailsUrl = `http://localhost:8001/api/service/reports/mediumriskfetch/${id}`;
        } else if(currentcategory == 'category4'){
            detailsUrl = `http://localhost:8001/api/service/reports/highriskfetch/${id}`;
        } else if(currentcategory == 'category5'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedfetch/${id}`;
        }

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                if(currentcategory == 'category1' || currentcategory == 'category2' || 
                    currentcategory == 'category3' || currentcategory == 'category4'){
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('kreportId').textContent = data.report_id;
                document.getElementById('kconcernId').textContent = data.concern_id;
                if(data.anonymity_status == 'non-anonymous'){
                    document.getElementById('kconcernedCitizen').textContent = data.fullname;
                } else if(data.anonymity_status == 'anonymous'){
                    document.getElementById('kconcernedCitizen').textContent = "Anonymous";
                }
                document.getElementById('kstoreId').textContent = data.store_id;
                document.getElementById('kstoreName').textContent = data.store_name;
                document.getElementById('kstoreAddress').textContent = data.store_address;
                document.getElementById('kstoreRecords').textContent = data.record_status;
                document.getElementById('kreportDetails').textContent = data.report_details;
                document.getElementById('kstoreViolations').textContent = data.store_violations;
                document.getElementById('kcreateAt').textContent = data.create_at;
                document.getElementById('kreportStatus').textContent = data.report_status;
                document.getElementById("kfile1").innerHTML = '';
            } else if(currentcategory == 'category5'){
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('cactionId').textContent = data.action_id;
                document.getElementById('creportId').textContent = data.report_id;
                document.getElementById('ccreateAt').textContent = data.create_at;
                document.getElementById('cstoreName').textContent = data.store_name;
                document.getElementById('cstoreAddress').textContent = data.store_address;
                document.getElementById('cactions').textContent = data.actions;
                document.getElementById('cstaff').textContent = data.staff;
                document.getElementById("cafile1").innerHTML = '';
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
                if(currentcategory == 'category1' || currentcategory == 'category2' || 
                    currentcategory == 'category3' || currentcategory == 'category4'){
                        displayFile(data.file1, "kfile1");
                } else if(currentcategory == 'category5'){
                        displayFile(data.afile1, "cafile1");
                }
            })
            .catch(error => console.error('Error fetching details:', error));
    }

    function openReportModal() {
        document.getElementById("reportmodal").style.display = "block";
        document.body.classList.add('modal-open'); // Disable body scrolling
    }
      
    function closeReportModal() {
    document.getElementById("reportmodal").style.display = "none";
    document.body.classList.remove('modal-open'); // Re-enable body scrolling
    //window.location.reload();
    }

    function openActionModal() {
        document.getElementById("actionmodal").style.display = "block";
        document.body.classList.add('modal-open'); // Disable body scrolling
    }
      
    function closeActionModal() {
    document.getElementById("actionmodal").style.display = "none";
    document.body.classList.remove('modal-open'); // Re-enable body scrolling
    //window.location.reload();
    }

    function updateReportContent(id) {
        document.getElementById("dateParagraph").innerHTML = '';
        document.getElementById("reportParagraph").innerHTML = '';
        document.getElementById("reportImage").src = '';

        // Fetch the data from an API or server
        fetch(`http://localhost:8001/api/service/reports/reportdetails/${id}`)
          .then(response => response.json()) // Parse the JSON response
          .then(data => {
            // Format the date to "Month Day, Year" (e.g., "February 25, 2025")
            const createAtDate = new Date(data[0].create_at);
            const formattedDate = createAtDate.toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            });
            
            document.getElementById("dateParagraph").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Date: ${formattedDate}</span>`;

            document.getElementById("storename").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Reported store: ${data[0].store_name}</span>`;

            document.getElementById("storeaddress").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Address: ${data[0].store_address}</span>`;
            
            // Update the report text dynamically from 'report_details' field of JSON
            document.getElementById("reportParagraph").innerHTML = `<span style="font-family:Cambria;font-size:16px;">&nbsp; &nbsp;<span style="font-family:Cambria;font-weight:normal;font-size:16px;">${data[0].report_details}</span></span>`;
            
            // Update the image source dynamically from 'file1' field of JSON
            const imageUrl = data[0].file1;
            document.getElementById("reportImage").src = imageUrl;
          })
          .catch(error => {
            console.error('Error fetching data:', error);
        });
    }
      
      function updateActionContent(id) {
        document.getElementById("dateConducted").innerHTML = '';
        document.getElementById("actionStaff").innerHTML = '';
        document.getElementById("actionConducted").innerHTML = '';
        document.getElementById("actionImage").src = '';

        // Fetch the data from an API or server
        fetch(`http://localhost:8001/api/service/reports/resolvedfetch/${id}`)
          .then(response => response.json()) // Parse the JSON response
          .then(data => {
            // Format the date to "Month Day, Year" (e.g., "February 25, 2025")
            const createAtDate = new Date(data.create_at);
            const formattedDate = createAtDate.toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric'
            });
            
            document.getElementById("dateConducted").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Date: ${formattedDate}</span>`;

            document.getElementById("store").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Action for: ${data.store_name}</span>`;

            document.getElementById("address").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Address: ${data.store_address}</span>`;

            document.getElementById("actionStaff").innerHTML = `<span style="font-family:Cambria;font-size:15px;">Staff: ${data.staff}</span>`;
            
            // Update the report text dynamically from 'report_details' field of JSON
            document.getElementById("actionConducted").innerHTML = `<span style="font-family:Cambria;font-size:16px;">&nbsp; &nbsp;<span style="font-family:Cambria;font-weight:normal;font-size:16px;">${data.actions}</span></span>`;
            
            // Update the image source dynamically from 'file1' field of JSON
            const imageUrl = data.afile1;
            document.getElementById("actionImage").src = imageUrl;
          })
          .catch(error => {
            console.error('Error fetching data:', error);
        });
    }
      

   
    
    

    //Kagawad create report
    // Toggle visibility of the report section and hide "Create Report" button when the "Create Report" button is clicked
    document.getElementById('kcreateReportBtn').addEventListener('click', function() {
        var reportSection = document.querySelector('.report-section');
        var createReportBtn = document.getElementById('kcreateReportBtn');
        
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
    document.getElementById('kcancelReport').addEventListener('click', function() {
        var reportSection = document.querySelector('.report-section');
        var createReportBtn = document.getElementById('kcreateReportBtn');
        reportSection.style.display = 'none'; // Hide the report section
        createReportBtn.style.display = 'block'; // Show the "Create Report" button
    });

    //Kagawad submit report
    document.getElementById('ksubmitReport').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('kreportTxtErr').innerHTML = '';
        document.getElementById('kstatusSelectErr').innerHTML = '';
        document.getElementById('kstaffSelectErr').innerHTML = '';
        document.getElementById('kimage1Err').innerHTML = '';
        const concernId = document.getElementById('kconcernId').innerText;
        const reportId = document.getElementById('kreportId').innerText;
        const storeId = document.getElementById('kstoreId').innerText;
        const reportText = document.getElementById('kreportTextarea').value;
        const statusSelect = document.getElementById('kstatusSelect').value;
        const staffSelect = document.getElementById('kstaffSelect').value;
        const image1 = document.getElementById('kimage1').files[0];

    let isValid = true;

    if(reportText == ''){
        document.getElementById('kreportTxtErr').innerHTML = 'Enter report.';
        isValid = false;
    }

    if(staffSelect == ''){
        document.getElementById('kstaffSelectErr').innerHTML = 'Enter staff.';
        isValid = false;
    }

    if(statusSelect == ''){
        document.getElementById('kstatusSelectErr').innerHTML = 'Enter status.';
        isValid = false;
    }

    if(!image1){
        document.getElementById('kimage1Err').innerHTML = 'Upload image1.';
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
        formData.append('staffSelect', staffSelect);
        formData.append('image1', image1);

            detailsUrl = `http://localhost:8001/api/service/reports/actionreportpost`;

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
                        document.getElementById('kreportId').textContent = '';
                        document.getElementById('kconcernId').textContent = '';
                        document.getElementById('kstoreId').textContent = '';
                        document.getElementById('kstoreName').textContent = '';
                        document.getElementById('kstoreAddress').textContent = '';
                        document.getElementById('kreportDetails').textContent = '';
                        document.getElementById('kstoreViolations').textContent = '';
                        document.getElementById('kcreateAt').textContent = '';
                        document.getElementById('kreportStatus').textContent = '';
                        document.getElementById('krstatusReason').value = '';
                        document.getElementById('kreportTextarea').value = '';
                        document.getElementById('kstatusSelect').value = '';
                        fetchTable(currentcategory, searchinput);
                        document.getElementById("kactionReport").reset();
                        document.getElementById('kclosePreviewBtn').click();
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
                            document.getElementById('kreportId').textContent = '';
                            document.getElementById('kconcernId').textContent = '';
                            document.getElementById('kstoreId').textContent = '';
                            document.getElementById('kstoreName').textContent = '';
                            document.getElementById('kstoreAddress').textContent = '';
                            document.getElementById('kreportDetails').textContent = '';
                            document.getElementById('kstoreViolations').textContent = '';
                            document.getElementById('kcreateAt').textContent = '';
                            document.getElementById('kreportStatus').textContent = '';
                            document.getElementById('krstatusReason').value = '';
                            document.getElementById('kreportTextarea').value = '';
                            document.getElementById('kstatusSelect').value = '';
                            fetchTable(currentcategory, searchinput);
                            document.getElementById("kactionReport").reset();
                            document.getElementById('kclosePreviewBtn').click();                        }
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
                    document.getElementById('kreportId').textContent = '';
                    document.getElementById('kconcernId').textContent = '';
                    document.getElementById('kstoreId').textContent = '';
                    document.getElementById('kstoreName').textContent = '';
                    document.getElementById('kstoreAddress').textContent = '';
                    document.getElementById('kreportDetails').textContent = '';
                    document.getElementById('kstoreViolations').textContent = '';
                    document.getElementById('kcreateAt').textContent = '';
                    document.getElementById('kreportStatus').textContent = '';
                    document.getElementById('krstatusReason').value = '';
                    document.getElementById('kreportTextarea').value = '';
                    document.getElementById('kstatusSelect').value = '';
                    fetchTable(currentcategory, searchinput);
                    document.getElementById("kactionReport").reset();
                    document.getElementById('kclosePreviewBtn').click();
                }
            });
        });
    }
});

    // Search function
    function search(searchTerm){
        const tableBody = document.querySelector("#table tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/reports/ssearch/${searchTerm}`;
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

    fetchTable(currentcategory, searchinput);