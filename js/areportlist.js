let currentcategory = 'category5';
let filter = null;
let searchinput = '';
//event listeners
document.getElementById('staffSelect').addEventListener('change', function(){
    const staffSelect = document.getElementById('staffSelect').value;
    if(staffSelect == 'showK1'){
    currentcategory = 'category1';
    fetchTable(currentcategory, searchinput);
    } else if(staffSelect == 'showK2'){
        currentcategory = 'category2';
        fetchTable(currentcategory, searchinput);
    } else if(staffSelect == 'showK3'){
        currentcategory = 'category3';
        fetchTable(currentcategory, searchinput);
    } else if(staffSelect == 'showC'){
        currentcategory = 'category4';
        fetchTable(currentcategory, searchinput);
    } else if(staffSelect == 'showCt'){
        currentcategory = 'category5';
        fetchTable(currentcategory, searchinput);
    } else if(staffSelect == 'showUpdates'){
        currentcategory = 'category6';
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
   } else if (currentcategory == 'category6'){
    fetchTable(currentcategory, searchinput);
   }
});

function fetchTable(currentcategory, searchinput){
    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    let detailsUrl = null;
    if(searchinput == ''){
        if(currentcategory == 'category1'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k1fetchReports`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k2fetchReports`;
        } else if(currentcategory == 'category3'){
            detailsUrl = 'http://localhost:8001/api/service/concernslist/k3fetchReports';
        } else if(currentcategory == 'category4'){
            detailsUrl = 'http://localhost:8001/api/service/concernslist/sfetchReports';
        } else if(currentcategory == 'category5'){
            detailsUrl = 'http://localhost:8001/api/service/concernslist/ctfetchReports';
        } else if(currentcategory == 'category6'){
            detailsUrl = 'http://localhost:8001/api/service/concernslist/reportUpdates';
        }
    } else{
        if(currentcategory == 'category1'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k1search/${searchinput}`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k2search/${searchinput}`;
        } else if(currentcategory == 'category3'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k3search/${searchinput}`;
        } else if(currentcategory == 'category4'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/ssearch/${searchinput}`;
        } else if(currentcategory == 'category5'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/ctsearch/${searchinput}`;
        } else if(currentcategory == 'category6'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/updatesSearch/${searchinput}`;
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

                const createdAtCell = document.createElement("td");
                createdAtCell.textContent = item.create_at;
                row.appendChild(createdAtCell);

                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "View";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");

                if((currentcategory == 'category1') || (currentcategory == 'category2') || (currentcategory == 'category3')){
                button.setAttribute("data-bs-target", "#kpreviewModal");
                } else if(currentcategory == 'category4'){
                    button.setAttribute("data-bs-target", "#cpreviewModal");
                } else if((currentcategory == 'category5') || (currentcategory == 'category6')){
                    button.setAttribute("data-bs-target", "#ctpreviewModal");
                }

                // Add event listener to the button
                button.addEventListener('click', function() {
                    if((currentcategory == 'category1') || (currentcategory == 'category2') || (currentcategory == 'category3')){
                        kfetchItemDetails(item.report_id);
                    } else if(currentcategory == 'category4'){
                        cfetchItemDetails(item.report_id);
                    } else if((currentcategory == 'category5') || (currentcategory == 'category6')){
                        ctfetchItemDetails(currentcategory, item.report_id);
                    }
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    function kfetchItemDetails(id) {
        if(currentcategory == 'category1'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k1fetch/${id}`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k2fetch/${id}`;
        } else if(currentcategory == 'category3'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k3fetch/${id}`;
        }

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('kreportId').textContent = data.report_id;
                document.getElementById('kconcernId').textContent = data.concern_id;
                document.getElementById('kstoreId').textContent = data.store_id;
                document.getElementById('kstoreName').textContent = data.store_name;
                document.getElementById('kstoreAddress').textContent = data.store_address;
                document.getElementById('kreportDetails').textContent = data.report_details;
                document.getElementById('kstoreViolations').textContent = data.store_violations;
                document.getElementById('kcreateAt').textContent = data.create_at;
                document.getElementById('kreportStatus').textContent = data.report_status;
                document.getElementById('krstatusReason').textContent = data.rstatus_reason;
                document.getElementById("kfile1").innerHTML = '';
                document.getElementById("kfile2").innerHTML = '';
                document.getElementById("kfile3").innerHTML = '';

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
                displayFile(data.file1, "kfile1");
                displayFile(data.file2, "kfile2");
                displayFile(data.file3, "kfile3");
            })
            .catch(error => console.error('Error fetching details:', error));
    }

   function cfetchItemDetails(id) {

        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concernslist/sfetch/${id}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('creportId').textContent = data.report_id;
                document.getElementById('cconcernId').textContent = data.concern_id;
                document.getElementById('cstoreId').textContent = data.store_id;
                document.getElementById('cstoreName').textContent = data.store_name;
                document.getElementById('cstoreAddress').textContent = data.store_address;
                document.getElementById('creportDetails').textContent = data.report_details;
                document.getElementById('cstoreViolations').textContent = data.store_violations;
                document.getElementById('ccreateAt').textContent = data.create_at;
                document.getElementById('creportStatus').textContent = data.report_status;
                document.getElementById('crstatusReason').textContent = data.rstatus_reason;
                document.getElementById("cfile1").innerHTML = '';
                document.getElementById("cfile2").innerHTML = '';
                document.getElementById("cfile3").innerHTML = '';
                document.getElementById('cpreviousActions').textContent = data.actions;
                document.getElementById("cafile1").innerHTML = '';
                document.getElementById("cafile2").innerHTML = '';
                document.getElementById("cafile3").innerHTML = '';

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
                displayFile(data.file1, "cfile1");
                displayFile(data.file2, "cfile2");
                displayFile(data.file3, "cfile3");
                displayFile(data.afile1, "cafile1");
                displayFile(data.afile2, "cafile2");
                displayFile(data.afile3, "cafile3");
            })
            .catch(error => console.error('Error fetching details:', error));
    }
    
    function ctfetchItemDetails(currentcategory, id) {

        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concernslist/ctfetch/${id}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                //document.getElementById("previewId").innerText = data.id;
                // Populate HTML with dynamic data
                document.getElementById('ctreportId').textContent = data.report_id;
                document.getElementById('ctconcernId').textContent = data.concern_id;
                document.getElementById('ctstoreId').textContent = data.store_id;
                document.getElementById('ctstoreName').textContent = data.store_name;
                document.getElementById('ctstoreAddress').textContent = data.store_address;
                document.getElementById('ctreportDetails').textContent = data.report_details;
                document.getElementById('ctstoreViolations').textContent = data.store_violations;
                document.getElementById('ctcreateAt').textContent = data.create_at;
                document.getElementById('ctreportStatus').textContent = data.report_status;
                document.getElementById('ctrstatusReason').textContent = data.rstatus_reason;
                document.getElementById("ctfile1").innerHTML = '';
                document.getElementById("ctfile2").innerHTML = '';
                document.getElementById("ctfile3").innerHTML = '';
                document.getElementById('ctpreviousActions').textContent = data.k_action;
                document.getElementById("ctafile1").innerHTML = '';
                document.getElementById("ctafile2").innerHTML = '';
                document.getElementById("ctafile3").innerHTML = '';
                document.getElementById('ctcpreviousActions').textContent = data.c_action;
                document.getElementById("ctcafile1").innerHTML = '';
                document.getElementById("ctcafile2").innerHTML = '';
                document.getElementById("ctcafile3").innerHTML = '';
                if(currentcategory == 'category5'){
                document.getElementById("ctcreateReportBtn").style.display = 'block';
                } else if(currentcategory == 'category6'){
                document.getElementById("ctcreateReportBtn").style.display = 'none';
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
                displayFile(data.file1, "ctfile1");
                displayFile(data.file2, "ctfile2");
                displayFile(data.file3, "ctfile3");
                displayFile(data.k_file1, "ctafile1");
                displayFile(data.k_file2, "ctafile2");
                displayFile(data.k_file3, "ctafile3");
                displayFile(data.c_file1, "ctcafile1");
                displayFile(data.c_file2, "ctcafile2");
                displayFile(data.c_file3, "ctcafile3");
            })
            .catch(error => console.error('Error fetching details:', error));
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
        document.getElementById('kimage1Err').innerHTML = '';
        document.getElementById('kimage2Err').innerHTML = '';
        document.getElementById('kimage3Err').innerHTML = '';
        const concernId = document.getElementById('kconcernId').innerText;
        const reportId = document.getElementById('kreportId').innerText;
        const storeId = document.getElementById('kstoreId').innerText;
        const reportText = document.getElementById('kreportTextarea').value;
        const statusSelect = document.getElementById('kstatusSelect').value;
        const image1 = document.getElementById('kimage1').files[0];
        const image2 = document.getElementById('kimage2').files[0];
        const image3 = document.getElementById('kimage3').files[0];

    let isValid = true;

    if(reportText == ''){
        document.getElementById('kreportTxtErr').innerHTML = 'Enter report.';
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

    if(!image2){
        document.getElementById('kimage2Err').innerHTML = 'Upload image2.';
        isValid = false;
    }

    if(!image3){
        document.getElementById('kimage3Err').innerHTML = 'Upload image3.';
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

        let detailsUrl = null;
        if(currentcategory == 'category1'){
        detailsUrl = `http://localhost:8001/api/service/concernslist/k1post`;
        } else if(currentcategory == 'category2'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k2post`;
        } else if(currentcategory == 'category3'){
            detailsUrl = `http://localhost:8001/api/service/concernslist/k3post`;
        }
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

    //Captain create report
    // Toggle visibility of the report section and hide "Create Report" button when the "Create Report" button is clicked
    document.getElementById('ccreateReportBtn').addEventListener('click', function() {
        var reportSection = document.querySelector('.creport-section');
        var createReportBtn = document.getElementById('ccreateReportBtn');
        
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
    document.getElementById('ccancelReport').addEventListener('click', function() {
        var reportSection = document.querySelector('.creport-section');
        var createReportBtn = document.getElementById('ccreateReportBtn');
        reportSection.style.display = 'none'; // Hide the report section
        createReportBtn.style.display = 'block'; // Show the "Create Report" button
    });

    //Captain submit report
    document.getElementById('csubmitReport').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('creportTxtErr').innerHTML = '';
        document.getElementById('cstatusSelectErr').innerHTML = '';
        document.getElementById('cimage1Err').innerHTML = '';
        document.getElementById('cimage2Err').innerHTML = '';
        document.getElementById('cimage3Err').innerHTML = '';
        const concernId = document.getElementById('cconcernId').innerText;
        const reportId = document.getElementById('creportId').innerText;
        const storeId = document.getElementById('cstoreId').innerText;
        const reportText = document.getElementById('creportTextarea').value;
        const statusSelect = document.getElementById('cstatusSelect').value;
        const image1 = document.getElementById('cimage1').files[0];
        const image2 = document.getElementById('cimage2').files[0];
        const image3 = document.getElementById('cimage3').files[0];

    let isValid = true;

    if(reportText == ''){
        document.getElementById('creportTxtErr').innerHTML = 'Enter report.';
        isValid = false;
    }

    if(statusSelect == ''){
        document.getElementById('cstatusSelectErr').innerHTML = 'Enter status.';
        isValid = false;
    }

    if(!image1){
        document.getElementById('cimage1Err').innerHTML = 'Upload image1.';
        isValid = false;
    }

    if(!image2){
        document.getElementById('cimage2Err').innerHTML = 'Upload image2.';
        isValid = false;
    }

    if(!image3){
        document.getElementById('cimage3Err').innerHTML = 'Upload image3.';
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
                        document.getElementById('creportId').textContent = '';
                        document.getElementById('cconcernId').textContent = '';
                        document.getElementById('cstoreId').textContent = '';
                        document.getElementById('cstoreName').textContent = '';
                        document.getElementById('cstoreAddress').textContent = '';
                        document.getElementById('creportDetails').textContent = '';
                        document.getElementById('cstoreViolations').textContent = '';
                        document.getElementById('ccreateAt').textContent = '';
                        document.getElementById('creportStatus').textContent = '';
                        document.getElementById('crstatusReason').value = '';
                        document.getElementById('cpreviousActions').value = '';
                        document.getElementById('creportTextarea').value = '';
                        document.getElementById('cstatusSelect').value = '';
                        fetchTable(currentcategory, searchinput);
                        document.getElementById("cactionReport").reset();
                        document.getElementById('cclosePreviewBtn').click();
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
                            document.getElementById('creportId').textContent = '';
                            document.getElementById('cconcernId').textContent = '';
                            document.getElementById('cstoreId').textContent = '';
                            document.getElementById('cstoreName').textContent = '';
                            document.getElementById('cstoreAddress').textContent = '';
                            document.getElementById('creportDetails').textContent = '';
                            document.getElementById('cstoreViolations').textContent = '';
                            document.getElementById('ccreateAt').textContent = '';
                            document.getElementById('creportStatus').textContent = '';
                            document.getElementById('crstatusReason').value = '';
                            document.getElementById('cpreviousActions').value = '';
                            document.getElementById('creportTextarea').value = '';
                            document.getElementById('cstatusSelect').value = '';
                            fetchTable(currentcategory, searchinput);
                            document.getElementById("cactionReport").reset();
                            document.getElementById('cclosePreviewBtn').click();                        }
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
                    document.getElementById('creportId').textContent = '';
                    document.getElementById('cconcernId').textContent = '';
                    document.getElementById('cstoreId').textContent = '';
                    document.getElementById('cstoreName').textContent = '';
                    document.getElementById('cstoreAddress').textContent = '';
                    document.getElementById('creportDetails').textContent = '';
                    document.getElementById('cstoreViolations').textContent = '';
                    document.getElementById('ccreateAt').textContent = '';
                    document.getElementById('creportStatus').textContent = '';
                    document.getElementById('crstatusReason').value = '';
                    document.getElementById('cpreviousActions').value = '';
                    document.getElementById('creportTextarea').value = '';
                    document.getElementById('cstatusSelect').value = '';
                    fetchTable(currentcategory, searchinput);
                    document.getElementById("cactionReport").reset();
                    document.getElementById('cclosePreviewBtn').click();
                }
            });
        });
    }
    });

    //City create report
    // Toggle visibility of the report section and hide "Create Report" button when the "Create Report" button is clicked
    document.getElementById('ctcreateReportBtn').addEventListener('click', function() {
        forwardToCity();
    });

    //City submit report
    function forwardToCity(){
        const reportId = document.getElementById('ctreportId').innerText;
        const reportStatus = 'Forwarded to cityhall.';
        const statusReason = 'The report is not resolved by the barangay, it is forwared to city.';

        // Create a FormData object to send the file
        const formData = new FormData();
        formData.append('reportId', reportId);
        formData.append('reportStatus', reportStatus);
        formData.append('statusReason', statusReason);

        const detailsUrl = `http://localhost:8001/api/service/concernslist/ctpost`;
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
                        document.getElementById('ctreportId').textContent = '';
                        document.getElementById('ctconcernId').textContent = '';
                        document.getElementById('ctstoreId').textContent = '';
                        document.getElementById('ctstoreName').textContent = '';
                        document.getElementById('ctstoreAddress').textContent = '';
                        document.getElementById('ctreportDetails').textContent = '';
                        document.getElementById('ctstoreViolations').textContent = '';
                        document.getElementById('ctcreateAt').textContent = '';
                        document.getElementById('ctreportStatus').textContent = '';
                        document.getElementById('ctrstatusReason').value = '';
                        document.getElementById('ctpreviousActions').value = '';
                        document.getElementById('ctreportTextarea').value = '';
                        fetchTable(currentcategory, searchinput);
                        document.getElementById("ctactionReport").reset();
                        document.getElementById('ctclosePreviewBtn').click();
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
                            document.getElementById('ctreportId').textContent = '';
                            document.getElementById('ctconcernId').textContent = '';
                            document.getElementById('ctstoreId').textContent = '';
                            document.getElementById('ctstoreName').textContent = '';
                            document.getElementById('ctstoreAddress').textContent = '';
                            document.getElementById('ctreportDetails').textContent = '';
                            document.getElementById('ctstoreViolations').textContent = '';
                            document.getElementById('ctcreateAt').textContent = '';
                            document.getElementById('ctreportStatus').textContent = '';
                            document.getElementById('ctrstatusReason').value = '';
                            document.getElementById('ctpreviousActions').value = '';
                            document.getElementById('ctreportTextarea').value = '';
                            fetchTable(currentcategory, searchinput);
                            document.getElementById("ctactionReport").reset();
                            document.getElementById('ctclosePreviewBtn').click();                        }
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
                    document.getElementById('ctreportId').textContent = '';
                    document.getElementById('ctconcernId').textContent = '';
                    document.getElementById('ctstoreId').textContent = '';
                    document.getElementById('ctstoreName').textContent = '';
                    document.getElementById('ctstoreAddress').textContent = '';
                    document.getElementById('ctreportDetails').textContent = '';
                    document.getElementById('ctstoreViolations').textContent = '';
                    document.getElementById('ctcreateAt').textContent = '';
                    document.getElementById('ctreportStatus').textContent = '';
                    document.getElementById('ctrstatusReason').value = '';
                    document.getElementById('ctpreviousActions').value = '';
                    document.getElementById('ctreportTextarea').value = '';
                    fetchTable(currentcategory, searchinput);
                    document.getElementById("ctactionReport").reset();
                    document.getElementById('ctclosePreviewBtn').click();
                }
            });
        });
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

    // Toggle visibility of the report section and hide "Create Report" button when the "Create Report" button is clicked
    /*    document.getElementById('createReportBtn').addEventListener('click', function() {
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
    }); */

    // Submit the report when the submit button is clicked
    /*    document.getElementById('submitReport').addEventListener('click', function(event) {
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
    }); */

    fetchTable(currentcategory, searchinput);