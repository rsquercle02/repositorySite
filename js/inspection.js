function fetchBusiness(){
    const tableBody = document.querySelector("#marketsTable tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    // Fetch data from the API endpoint to populate the table
    fetch('https://bfmsi.smartbarangayconnect.com/api/service/inspection/fetchschedule')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#marketsTable tbody");
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
                descriptionCell.textContent = `${item.inspectionDate} ${item.assignedDay}`;
                row.appendChild(descriptionCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "View Data";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#verificationModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.businessId);
                    document.getElementById('inspectiondetails').style.display = 'block';
                    document.getElementById('inspectioncon').style.display = 'none';
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    //search item
    function search(searchTerm){
        const tableBody = document.querySelector("#marketsTable tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Fetch data from the API endpoint to populate the table
    fetch(`https://bfmsi.smartbarangayconnect.com/api/service/inspection/searchinspection/${searchTerm}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#marketsTable tbody");
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
                descriptionCell.textContent = `${item.inspectionDate} ${item.assignedDay}`;
                row.appendChild(descriptionCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "View Data";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#verificationModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.businessId);
                    document.getElementById('inspectiondetails').style.display = 'block';
                    document.getElementById('inspectioncon').style.display = 'none';
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
    
    const backbtn = document.getElementById('backbtn');
    backbtn.addEventListener('click', function() {
        document.getElementById('inspectioncon').style.display = 'block';
        document.getElementById('inspectiondetails').style.display = 'none';
        document.getElementById("businessId").innerText = '';
        document.getElementById("businessName").innerText = '';
        document.getElementById("businessType").innerText = '';
        document.getElementById("businessLocation").innerText = '';
        document.getElementById("inspectionDate").innerText = '';
        document.getElementById("assignedInspectors").innerText = '';
    });

    function fetchItemDetails(itemId) {
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/inspection/fetchinspectiondetails/${itemId}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {

                //if the business is already inspected.
                if (!data || Object.keys(data).length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'View data canceled!',
                        text: 'The market has already been inspected.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            backbtn.click();
                            fetchBusiness();
                        }
                    });
                    return; // Stop execution if no data
                }

                document.getElementById("businessId").innerText = data.businessId;
                document.getElementById("businessName").innerText = data.businessName;
                document.getElementById("businessType").innerText = data.businessType;
                document.getElementById("businessLocation").innerText = data.Location;
                document.getElementById("inspectionDate").innerText = data.inspectionDate;
                document.getElementById("assignedInspectors").innerText = data.assignedInspectors;

                const inspectbtn = document.getElementById('inspectbtn');
                inspectbtn.addEventListener('click', function() {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "Inspecion will start.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, inspect."
                    }).then((result) => {
                        if (result.isConfirmed) {
                            formItemDetails(data.businessId);
                            document.getElementById('inspectionform').style.display = 'block';
                            document.getElementById('inspectiondetails').style.display = 'none';
                        }
                    });
                });
            })
    }

    function formItemDetails(itemId){
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/inspection/forminspectiondetails/${itemId}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                document.getElementById("business_id").value = data.businessId;
                document.getElementById("business_name").value = data.businessName;
                document.getElementById("business_type").value = data.businessType;
                document.getElementById("inspection_date").value = data.inspectionDate;
                document.getElementById("inspector_name").value = data.assignedInspectors;
            })
    }

    const inspectionback = document.getElementById('inspectionback');
    inspectionback.addEventListener('click', function() {
        Swal.fire({
            title: "Are you sure?",
            text: "Inspection will be canceled.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Cancel",
            cancelButtonText: "Continue"
          }).then((result) => {
            if (result.isConfirmed) {
        document.getElementById('inspectiondetails').style.display = 'block';
        document.getElementById('inspectionform').style.display = 'none';
            }
        });
    });

    const detailsUrl = "https://bfmsi.smartbarangayconnect.com/api/service/inspection/fetchcriteria";
    const criteriaContainer = document.getElementById("criteriaContainer");

    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error("Invalid data format", data);
                return;
            }

            let categories = {};
            data.forEach(item => {
                if (!categories[item.category]) {
                    categories[item.category] = [];
                }
                categories[item.category].push(item);
            });

            Object.keys(categories).forEach(category => {
                const categoryHeader = document.createElement("div");
                categoryHeader.className = "category";
                categoryHeader.textContent = category;
                criteriaContainer.appendChild(categoryHeader);

                categories[category].forEach(item => {
                    const formGroup = document.createElement("div");
                    formGroup.className = "form-group";

                    const label = document.createElement("label");
                    label.textContent = item.question;

                    let input;
                    if (["text", "number", "date", "email", "password"].includes(item.inputType)) {
                        input = document.createElement("input");
                        input.type = item.inputType;
                    } else if (item.inputType === "textarea") {
                        input = document.createElement("textarea");
                    } else if (item.inputType === "select") {
                        input = document.createElement("select");
                        ["Yes", "No", "N/A"].forEach(optionText => {
                            const option = document.createElement("option");
                            option.value = optionText;
                            option.textContent = optionText;
                            input.appendChild(option);
                        });
                    } else if (item.inputType === "radio" || item.inputType === "checkbox") {
                        input = document.createElement("div");
                        ["Yes", "No", "N/A"].forEach(optionText => {
                            const inputElement = document.createElement("input");
                            inputElement.type = item.inputType;
                            inputElement.name = item.criteria_id;
                            inputElement.value = optionText;

                            const inputLabel = document.createElement("label");
                            inputLabel.textContent = optionText;

                            const wrapper = document.createElement("div");
                            wrapper.appendChild(inputElement);
                            wrapper.appendChild(inputLabel);

                            input.appendChild(wrapper);
                        });
                    }

                    input.name = item.criteria_id; // Use question_id as input name

                    const error = document.createElement("span");
                    error.className = "error";
                    error.textContent = "This field is required";

                    formGroup.appendChild(label);
                    formGroup.appendChild(input);
                    formGroup.appendChild(error);

                    criteriaContainer.appendChild(formGroup);
                });
            });
        })
        .catch(error => console.error("Error fetching criteria:", error));

    document.getElementById("submitBtn").addEventListener("click", function (e) {
        e.preventDefault();
        let valid = true;
        let formData = {};

        // Collect extra inputs
        formData["business_id"] = document.getElementById("business_id").value.trim();
        formData["inspection_date"] = document.getElementById("inspection_date").value.split(",")[0].trim();
        formData["inspector_name"] = document.getElementById("inspector_name").value.trim();
        formData["business_name"] = document.getElementById("business_name").value.trim();
        formData["business_type"] = document.getElementById("business_type").value.trim();

        // Collect dynamic form inputs
        document.querySelectorAll(".form-group").forEach(group => {
            const inputs = group.querySelectorAll("input, select, textarea");
            const error = group.querySelector(".error"); // Find error span
            let filled = false;
            let value = "";

            inputs.forEach(input => {
                if ((input.type === "radio" || input.type === "checkbox") && input.checked) {
                    filled = true;
                    value = input.value;
                } else if (input.type !== "radio" && input.type !== "checkbox" && input.value.trim()) {
                    filled = true;
                    value = input.value.trim();
                }
            });

            if (!filled && error) { // Check if error span exists before using it
                error.style.display = "block";
                valid = false;
            } else if (error) { // Hide error only if it exists
                error.style.display = "none";
                formData[inputs[0].name] = value;
            }
        });


        if (valid) {
            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to submit the form?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, submit it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("Form Data:", formData);
                    //alert("Form submitted successfully!");
                    fetch('https://bfmsi.smartbarangayconnect.com/api/service/inspection/inspectioninfo', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(formData)
                    })
                    .then(response => {
                        // Check if the response is okay (status 200-299)
                        if (response.ok) {
                            Swal.fire({
                                title: "Inspection complete!",
                                text: "The inspection data is submitted for approval.",
                                icon: "success",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    document.getElementById("inspectionForm").reset();
                                    document.getElementById('inspectioncon').style.display = 'block';
                                    document.getElementById('inspectiondetails').style.display = 'none';
                                    document.getElementById('inspectionform').style.display = 'none';
                                    fetchBusiness();

                                }
                            });
                        } else {
                            // If the response is not ok, parse the error response
                            return response.json().then(errorData => {
                                // Check for specific error message
                                if (errorData.error === 'Already inspected.') {
                                    Swal.fire({
                                        title: "Inspection Cancelled!",
                                        text: "Already inspected.",
                                        icon: "error",
                                        confirmButtonColor: "#0f0"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            document.getElementById("inspectionForm").reset();
                                            document.getElementById('inspectioncon').style.display = 'block';
                                            document.getElementById('inspectiondetails').style.display = 'none';
                                            document.getElementById('inspectionform').style.display = 'none';
                                            fetchBusiness();
                                        }
                                    });
                                }
                            });
                        }
                    })
                    //.then(data => console.log("Server response:", data))
                    .catch(error => console.error("Error submitting form:", error));
                }
            });
        }
    });

    fetchBusiness();