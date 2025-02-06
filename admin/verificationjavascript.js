//fetch
function fetchBusiness(){
    const tableBody = document.querySelector("#marketsTable tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows
    // Fetch data from the API endpoint to populate the table
    fetch('http://bfmsi.smartbarangayconnect.com/api-gateway/public/inspection/fetch')
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
                descriptionCell.textContent = item.businessDescription;
                row.appendChild(descriptionCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "Verify";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#verificationModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.businessId);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

        // Function to fetch the item details and show in a modal
    function fetchItemDetails(itemId) {
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api-gateway/public/inspection/fetchDocument/${itemId}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                document.getElementById("hiddenLabel").innerText = data.businessId;
                document.getElementById("businessName").innerText = data.businessName;
                document.getElementById("businessType").innerText = data.businessType;
                document.getElementById("barangayClearance").innerHTML = '';
                document.getElementById("businessPermit").innerHTML = '';
                document.getElementById("occupancyCertificate").innerHTML = '';
                document.getElementById("taxCertificate").innerHTML = '';
                
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
                    } else if (fileExtension === 'pdf') {
                        // If the file is a PDF, show an <embed> element
                        var embed = document.createElement('embed');
                        embed.src = fileUrl;  // Set the source to the file URL
                        embed.type = 'application/pdf';
                        embed.width = '100%';
                        embed.height = '600px';
                        fileContainer.appendChild(embed);
                    } else {
                        // If the file type is not supported, show a message
                        var unsupportedMessage = document.createElement('p');
                        unsupportedMessage.textContent = `Unsupported file type: ${fileUrl}`;
                        fileContainer.appendChild(unsupportedMessage);
                    }
                }

                // Call the function for each file individually
                displayFile(data.barangayClearance, "barangayClearance");
                displayFile(data.businessPermit, "businessPermit");
                displayFile(data.occupancyCertificate, "occupancyCertificate");
                displayFile(data.taxCertificate, "taxCertificate");
            })
            .catch(error => console.error('Error fetching details:', error));
    }

    //search item
    function search(searchTerm){
        const tableBody = document.querySelector("#marketsTable tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; // This will remove all the previous rows
        // Fetch data from the API endpoint to populate the table
    fetch(`http://localhost:8001/api-gateway/public/inspection/search/${searchTerm}`)
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
                descriptionCell.textContent = item.businessDescription;
                row.appendChild(descriptionCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "Verify";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#verificationModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.businessId);
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
        const searchTerm = document.getElementById("searchMarket").value;
        if (searchTerm === '') {
            // If the search box is empty, display all the data
            fetchBusiness(); // `data` is your full data array
        } else {
            // Otherwise, update the table with filtered results based on the search term
            search(searchTerm);
        }
    });

    fetchBusiness();
    //setInterval(fetchBusiness, 5000);

//Verify button
//Approve button
// Select the button element by its ID
const aprvbtn = document.getElementById('approveBtn');

// Add an event listener for the 'click' event
aprvbtn.addEventListener('click', function() {
    const businessId = document.getElementById('hiddenLabel').textContent;
    Swal.fire({
    title: "Verify store documents?",
    text: "",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, verify."
    }).then((result) => {
        if (result.isConfirmed) {
            const dataToSend = {
                //businessId: businessId,
                businessStatus: "Verified, for schedule.",
                statusReason: "The store documents are valid."
            };

            // URL of the API (this can be any API endpoint)
            const apiUrl = `http://localhost:8001/api-gateway/public/inspection/status/${businessId}`; // Example API

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
                        title: "Store Verified!",
                        text: "It can be scheduled for inspection.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const closeBtn = document.getElementById('closeBtn');
                            closeBtn.click();
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
    const businessId = document.getElementById('hiddenLabel').textContent;
    Swal.fire({
    title: "Deny store documents?",
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
                businessStatus: "Denied",
                statusReason: "The store documents are not validated."
            };

            // URL of the API (this can be any API endpoint)
            const apiUrl = `http://localhost:8001/api-gateway/public/inspection/status/${businessId}`; // Example API

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
                        text: "The store documents are not validated.",
                        icon: "info",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const closeBtn = document.getElementById('closeBtn');
                            closeBtn.click();
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