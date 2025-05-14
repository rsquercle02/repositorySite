
function fetchTable(){
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';
    document.querySelector('.cardhtml').style.display = 'none';

    const cardContainer = document.querySelector("#cardContainer");
    cardContainer.innerHTML = ''; // Clear previous cards

    // Example URL for fetching detailed information (you may adjust it)
    const detailsUrl = 'http://localhost:8001/api/service/concerns/concernsReport';
    // Fetch data from the API endpoint to populate the table
    fetch(detailsUrl, {
        method: 'GET',
        credentials: 'include',
        })
        .then(response => response.json())
        .then(data => {
            const cardContainer = document.querySelector("#cardContainer");
            cardContainer.innerHTML = ''; // Clear previous cards
            
            if(data){
                insights(data);
            }

            data.forEach(item => {
                // Create card wrapper
                const cardCol = document.createElement("div");
                cardCol.classList.add("col-md-4"); // 3 cards per row on medium and up

                // Create card
                const card = document.createElement("div");
                card.classList.add("card", "h-100", "shadow-sm");

                // Card body
                const cardBody = document.createElement("div");
                cardBody.classList.add("card-body");

                // Card title
                const cardTitle = document.createElement("h5");
                cardTitle.classList.add("card-title");
                cardTitle.textContent = `Concern #${item.concern_id}`;

                // Card content
                const content = document.createElement("p");
                content.classList.add("mb-3");
                content.innerHTML = `
                    <strong>Store Name:</strong> ${item.store_name}<br>
                    <strong>Address:</strong> ${item.store_address}<br>
                    <strong>Resident Type:</strong> ${item.resident_type}<br>
                    <strong>Anonymity:</strong> ${item.anonymity_status}<br>
                    <strong>Date Created:</strong> ${item.create_at}
                `;

                // Button
                const button = document.createElement("button");
                button.textContent = "View";
                button.classList.add("btn", "btn-success", "mt-3");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#previewModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.concern_id);
                });

                // Assemble the card
                cardBody.appendChild(cardTitle);
                cardBody.appendChild(content);
                cardBody.appendChild(button);
                card.appendChild(cardBody);
                cardCol.appendChild(card);

                // Add the card to the container
                cardContainer.appendChild(cardCol);
            });
        })
        .catch(error => console.error('Error fetching data:', error))
        .finally(() => {
            // Hide loading
            document.getElementById('loading-indicator').style.display = 'none';
            document.querySelector('.cardhtml').style.display = 'block';
        });
    }

    function fetchItemDetails(id) {

        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/concerns/concernDetails/${id}`;

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
        // Show loading
        document.getElementById('loading-indicator').style.display = 'block';
        document.querySelector('.cardhtml').style.display = 'none';
        
        const cardContainer = document.querySelector("#cardContainer");
        cardContainer.innerHTML = ''; // Clear previous cards

        const detailsUrl = `http://localhost:8001/api/service/concerns/searchConcerns/${searchTerm}`;
        // Fetch data from the API endpoint to populate the table
        fetch(detailsUrl, {
            method: 'GET',
            credentials: 'include',
            })
            .then(response => response.json())
            .then(data => {
                const cardContainer = document.querySelector("#cardContainer");
                cardContainer.innerHTML = ''; // Clear previous cards

                data.forEach(item => {
                    // Create card wrapper
                    const cardCol = document.createElement("div");
                    cardCol.classList.add("col-md-4"); // 3 cards per row on medium and up
    
                    // Create card
                    const card = document.createElement("div");
                    card.classList.add("card", "h-100", "shadow-sm");
    
                    // Card body
                    const cardBody = document.createElement("div");
                    cardBody.classList.add("card-body");
    
                    // Card title
                    const cardTitle = document.createElement("h5");
                    cardTitle.classList.add("card-title");
                    cardTitle.textContent = `Concern #${item.concern_id}`;
    
                    // Card content
                    const content = document.createElement("p");
                    content.classList.add("mb-3");
                    content.innerHTML = `
                        <strong>Store Name:</strong> ${item.store_name}<br>
                        <strong>Address:</strong> ${item.store_address}<br>
                        <strong>Resident Type:</strong> ${item.resident_type}<br>
                        <strong>Anonymity:</strong> ${item.anonymity_status}<br>
                        <strong>Date Created:</strong> ${item.create_at}
                    `;
    
                    // Button
                    const button = document.createElement("button");
                    button.textContent = "View";
                    button.classList.add("btn", "btn-success", "mt-3");
                    button.setAttribute("data-bs-toggle", "modal");
                    button.setAttribute("data-bs-target", "#previewModal");
    
                    // Add event listener to the button
                    button.addEventListener('click', function() {
                        fetchItemDetails(item.concern_id);
                    });
    
                    // Assemble the card
                    cardBody.appendChild(cardTitle);
                    cardBody.appendChild(content);
                    cardBody.appendChild(button);
                    card.appendChild(cardBody);
                    cardCol.appendChild(card);
    
                    // Add the card to the container
                    cardContainer.appendChild(cardCol);
                });
            })
            .catch(error => console.error('Error fetching data:', error))
            .finally(() => {
                // Hide loading
                document.getElementById('loading-indicator').style.display = 'none';
                document.querySelector('.cardhtml').style.display = 'block';
            });
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

    // Preview modal event listener
    document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
        // Remove data of the form
        document.getElementById('concernId').textContent = '';
        document.getElementById('storeId').textContent = '';
        document.getElementById('storeName').textContent = '';
        document.getElementById('storeAddress').textContent = '';
        document.getElementById('concernDetails').textContent = '';
        document.getElementById('createAt').textContent = '';
        fetchTable();
        document.getElementById('closePreviewBtn').click();
    });

    /*************** Report reminder *************/

    // AI summary and suggestions for inspection data
    function insights(reportList){
        // Get the current date in the format YYYY-MM-DD
        const currentDate = new Date().toISOString().split('T')[0]; // Format: "YYYY-MM-DD"
    
        // Create the promptData with the current date and the passed reportList
        const promptData = {
            prompt: "Can you create a simple update in sentence type about the concerns list? Can you say the concern id on list that are no action, reported, resolved, not valid?",
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