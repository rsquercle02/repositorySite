function fetchBusiness(){
    const tableBody = document.querySelector("#marketsTable tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    // Fetch data from the API endpoint to populate the table
    fetch('https://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/approval')
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
                descriptionCell.textContent = item.inspection_date;
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
                    //fetchItemDetails(item.businessId, item.inspection_date);
                    fetchInspectionData(item.businessId, item.inspection_date);
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

    //search for business records
    function search(searchTerm){
        const tableBody = document.querySelector("#marketsTable tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Fetch data from the API endpoint to populate the table
    fetch(`https://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/searchapproval/${searchTerm}`)
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
                descriptionCell.textContent = item.inspection_date;
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
                    //fetchItemDetails(item.businessId, item.inspection_date);
                    fetchInspectioData(item.businessId, item.inspection_date);
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

    fetchBusiness();

    const backbtn = document.getElementById('backbtn');
    backbtn.addEventListener('click', function() {
        document.getElementById('inspectioncon').style.display = 'block';
        document.getElementById('inspectiondetails').style.display = 'none';
        document.getElementById('inspection-data').innerHTML = 'Loading...';
        document.getElementById('summaryandsuggestions').innerHTML = 'Loading...';
        document.getElementById('bId').innerHTML = 'N/A';
    });

        // Function to fetch data from the PHP API and populate the inspection data
        function fetchInspectionData(itemId, inspectiondate) {
            fetch(`https://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/fetchinspectionresults/${itemId}/${inspectiondate}`) // Fetch data from backend
                .then(function(response) {
                    return response.json(); // Convert response to JSON
                })
                .then(function(data) {
                    populateInspectionData(itemId, data); // Insert data into the from
                    //console.log("Can you summarize the data: " + JSON.stringify(data) + "?");
                    summaryandsuggestions(data);
                })
                .catch(function(error) {
                    console.error("Error fetching data:", error);
                    document.getElementById("inspection-data").innerHTML = "<p>Error loading data.</p>";
                });
        }

        // Function to organize and display inspection data
        function populateInspectionData(businessId, data) {
            const bId = document.getElementById("bId");
            bId.innerText = businessId;
            var container = document.getElementById("inspection-data");
            container.innerHTML = ""; // Clear existing content

            if (data.length === 0) {
                container.innerHTML = "<p>No inspection data found.</p>";
                return;
            }

            var groupedData = {};

            // Group data by category
            data.forEach(function(item) {
                if (!groupedData[item.category]) {
                    groupedData[item.category] = [];
                }
                groupedData[item.category].push(item);
            });

            // Iterate over each category and generate HTML
            for (var category in groupedData) {
                var categoryHTML = "<h5>" + category + "</h5><hr>";

                groupedData[category].forEach(function(item, index) {
                    var statusIcon = item.response.toLowerCase() === "yes" ? "✅" :
                                    item.response.toLowerCase() === "no" ? "❌" : "";

                    categoryHTML += `
                        <p><strong>${index + 1}. ${item.question}</strong> <br> 
                        <strong>Response:</strong> ${statusIcon} ${item.response}</p>
                    `;
                });

                categoryHTML += "<hr>"; // Add a line break after each category
                container.innerHTML += categoryHTML;
            }
        }

        // AI summary and suggestions for inspection data
        function summaryandsuggestions(data){
            //console.log("Can you summarize the data: " + JSON.stringify(data) + "?");
            const prompt = {
                prompt: "Can you create a summary of the inspection data and create suggestions for improvements" + JSON.stringify(data) + "?"
                };

            fetch('https://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/generatecontent', {
                method: 'POST',  // HTTP method
                headers: {
                    'Content-Type': 'application/json'  // Set content type to JSON
                },
                body: JSON.stringify(prompt)  // Convert the data to JSON
                })
                .then(response => response.json())  // Parse the response as JSON
                .then(data => {
                    const sasdata = data.candidates[0].content.parts[0].text;
                    //console.log(sasdata);
                    cleanAndFormatInspectionSummary(sasdata);
                    //document.getElementById('summaryandsuggestions').innerHTML = data.candidates[0].content.parts[0].text;
                })// Handle the response
                .catch(error => console.error('Error:', error));  // Handle errors

        }

        function cleanAndFormatInspectionSummary(data) {
          // Remove any asterisks used for bold formatting
          let cleanedData = data.replace(/\*\*(.*?)\*\*/g, '$1');  // Remove ** for bold text
          
          // Now clean up the text and format the sections
          let formattedText = cleanedData.replace(/\*/g, '-');  // Change * to bullet points
        
          // Format the Inspection Summary
          let inspectionSummaryStart = formattedText.indexOf('Inspection Summary');
          let areasRequiringImprovementStart = formattedText.indexOf('Areas Requiring Immediate Improvement');
          let suggestionsStart = formattedText.indexOf('Suggestions for Improvement');
        
          // Extract each section based on its position
          let inspectionSummary = formattedText.substring(inspectionSummaryStart, areasRequiringImprovementStart).trim();
          let areasRequiringImprovement = formattedText.substring(areasRequiringImprovementStart, suggestionsStart).trim();
          let suggestionsForImprovement = formattedText.substring(suggestionsStart).trim();
        
          // Combine the sections into a well-formatted output
          formattedText = `\n${inspectionSummary}\n\n\n\n\n\n\n\n\n\n` +
                          `**:\n\n\n\n\n\n\n` +
                          `${areasRequiringImprovement}\n\n\n\n\n\n\n\n\n\n` +
                          `**:\n\n\n\n\n\n\n` +
                          `${suggestionsForImprovement}`;
        
          //console.log(formattedText);
          document.getElementById('summaryandsuggestions').innerHTML = formattedText;
        }

        //Approve button
        // Select the button element by its ID
        const aprvbtn = document.getElementById('approveBtn');

        // Add an event listener for the 'click' event
        aprvbtn.addEventListener('click', function() {
            const businessId = document.getElementById('bId').textContent;
            Swal.fire({
            title: "Approve store?",
            text: "",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, approve."
            }).then((result) => {
                if (result.isConfirmed) {
                    const dataToSend = {
                        //businessId: businessId,
                        businessStatus: "Approved.",
                        statusReason: "The store is approved, certificate can be printed."
                    };

                    // URL of the API (this can be any API endpoint)
                    const apiUrl = `https://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/status/${businessId}`; // Example API

                    // Use fetch() to make the API call
                    fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                        },
                        body: JSON.stringify(dataToSend),
                    })
                    .then(response => {
                        // Check if the response is okay (status 200-299)
                        if (response.ok) {
                            Swal.fire({
                                title: "Store Approved!",
                                text: "Certificates can be printed.",
                                icon: "success",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const backbtn = document.getElementById('backbtn');
                                    backbtn.click();
                                    fetchBusiness();
                                }
                            });
                        } else {
                            // Handle the case where the response is not successful (status 4xx/5xx)
                            console.error("Failed to verify store: " + response.statusText);
                        }
                    })
                    .catch(error => {
                        // Handle any errors during the fetch request
                        console.error("Error:", error.message);
                    });
                }
            });
        });

        //Deny button
        // Select the button element by its ID
        const dnybtn = document.getElementById('denyBtn');

        // Add an event listener for the 'click' event
        dnybtn.addEventListener('click', function() {
            const businessId = document.getElementById('bId').textContent;
            Swal.fire({
            title: "Deny store?",
            text: "",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, deny."
            }).then((result) => {
                if (result.isConfirmed) {
                    const dataToSend = {
                        //businessId: businessId,
                        businessStatus: "Denied.",
                        statusReason: "The store did not comply with the inspection standards."
                    };

                    // URL of the API (this can be any API endpoint)
                    const apiUrl = `https://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/status/${businessId}`; // Example API

                    // Use fetch() to make the API call
                    fetch(apiUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                        },
                        body: JSON.stringify(dataToSend),
                    })
                    .then(response => {
                        // Check if the response is okay (status 200-299)
                        if (response.ok) {
                            Swal.fire({
                                title: "Store Denied!",
                                text: "Store did not comply the inspections standards.",
                                icon: "info",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const backbtn = document.getElementById('backbtn');
                                    backbtn.click();
                                    fetchBusiness();
                                }
                            });
                        } else {
                            // Handle the case where the response is not successful (status 4xx/5xx)
                            console.error("Failed to verify store: " + response.statusText);
                        }
                    })
                    .catch(error => {
                        // Handle any errors during the fetch request
                        console.error("Error:", error.message);
                    });
                }
            });
        });

        // Fetch and display inspection data when the page loads
        fetchInspectionData();