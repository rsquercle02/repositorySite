let currentcategory = null;
//event listeners
document.getElementById('showcategory1').addEventListener('click', function(){
    currentcategory = 'category1';
    //document.getElementById('showcategory1').style.display = 'block';
    document.getElementById('category2-filter').style.display = 'none';
    document.getElementById('category3-filter').style.display = 'none';
    document.getElementById('concernstatus').value = 'none.';
    document.getElementById('reportstatus').value = 'none.';
    fetchViolations(searchinput);
});

let filter = null;
let searchinput = '';
document.getElementById('showcategory2').addEventListener('click', function(){
    currentcategory = 'category2';
    document.getElementById('category2-filter').style.display = 'block';
    document.getElementById('category3-filter').style.display = 'none';
    document.getElementById('concernstatus').value = 'none.';
    document.getElementById('reportstatus').value = 'none.';
    filter = 'none.';
    fetchConcerns(filter, searchinput);
});

document.getElementById('concernstatus').addEventListener('change', function(){
    filter = document.getElementById('concernstatus').value;
    fetchConcerns(filter, searchinput);
});

document.getElementById('showcategory3').addEventListener('click', function(){
    currentcategory = 'category3';
    document.getElementById('category3-filter').style.display = 'block';
    document.getElementById('category2-filter').style.display = 'none';
    document.getElementById('concernstatus').value = 'none.';
    document.getElementById('reportstatus').value = 'none.';
    filter = 'none.';
    fetchReports(filter, searchinput);
});

document.getElementById('reportstatus').addEventListener('change', function(){
    filter = document.getElementById('reportstatus').value;
    fetchReports(filter, searchinput);
});

document.getElementById('searchinput').addEventListener('keyup', function(){
    searchinput = document.getElementById('searchinput').value;
    console.log(currentcategory);
   if (currentcategory == 'category1'){
    console.log(currentcategory);
    fetchViolations(searchinput);
   } else if (currentcategory == 'category2'){
    console.log(currentcategory);
    fetchConcerns(filter, searchinput);
   } else if (currentcategory == 'category3'){
    console.log(currentcategory);
    fetchReports(filter, searchinput);
   }
});

// Function to fetch and display the violation data as cards
function fetchViolations(searchinput) {
    let container = document.getElementById('card-container');
    container.innerHTML = '';
    let detailsUrl = null;
    if(searchinput == ''){
        detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/Violations`;
    } else {
        detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/Violations?search=${searchinput}`;
    }

    // Fetch data from the API endpoint
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('card-container');
            container.innerHTML = '';
            data.forEach(report => {
                const card = document.createElement('div');
                card.classList.add('card');
                card.classList.add('shadow-sm');
                card.classList.add('border-light');
                
                // Adding the content of the card dynamically
                card.innerHTML = `
                    <div class="card-body">
                    <h5 class="card-title">${report.store_name}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">${report.store_address}</h6>
                    <ul class="list-unstyled">
                        <li class="mb-3"><strong>Expired Products Count:</strong> ${report.expired_products_count}</li>
                        <li class="mb-3"><strong>Unhygienic Conditions Count:</strong> ${report.unhygienic_conditions_count}</li>
                        <li class="mb-3"><strong>Incorrect Labelling Count:</strong> ${report.incorrect_labelling_count}</li>
                        <li class="mb-3"><strong>Overpricing Count:</strong> ${report.overpricing_count}</li>
                        <li class="mb-3"><strong>Unsanitary Storage Count:</strong> ${report.unsanitary_storage_count}</li>
                        <li class="mb-3"><strong>Misleading Advertisement Count:</strong> ${report.misleading_advertisement_count}</li>
                        <li class="mb-3"><strong>Improper Packaging Count:</strong> ${report.improper_packaging_count}</li>
                        <li class="mb-3"><strong>Lack of Proper License Count:</strong> ${report.lack_of_proper_license_count}</li>
                        <li class="mb-3"><strong>Unsafe Food Handling Count:</strong> ${report.unsafe_food_handling_count}</li>
                        <li class="mb-3"><strong>Resolved:</strong> ${report.no_violations}</li>
                    </ul>
                </div>
                `;

                // Append the card to the container
                container.appendChild(card);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Function to fetch and display the concerns data as cards
function fetchConcerns(filter, searchinput) {
    let container = document.getElementById('card-container');
    container.innerHTML = '';
    let detailsUrl = null;
    if(searchinput == ''){
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/Concerns`;
        } else if (filter == 'No action.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/NoactionConcerns`;
        } else if (filter == 'Reported.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/ReportedConcerns`;
        } else if (filter == 'Resolved.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/ResolvedConcerns`;
        }
    } else {
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/Concerns?search=${searchinput}`;
        } else if (filter == 'No action.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/NoactionConcerns?search=${searchinput}`;
        } else if (filter == 'Reported.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/ReportedConcerns?search=${searchinput}`;
        } else if (filter == 'Resolved.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/ResolvedConcerns?search=${searchinput}`;
        }
    }

    // Fetch data from the API endpoint
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            container = document.getElementById('card-container');
            container.innerHTML = '';
            data.forEach(report => {
                const card = document.createElement('div');
                card.classList.add('card');
                card.classList.add('shadow-sm');
                card.classList.add('border-light');
                
                // Adding the content of the card dynamically
                card.innerHTML = `
                <div class="card-body">
                <h5 class="card-title">${report.store_name}</h5>
                <h6 class="card-subtitle mb-2 text-muted">${report.store_address}</h6>
                <p><strong>Concern Status:</strong> ${report.concern_status}</p>
                <p><strong>Concern ID:</strong> ${report.concern_id}</p>
                <p><strong>Concern Details:</strong></p>
                <p>${report.concern_details}</p>
                </div>
                `;

                // Append the card to the container
                container.appendChild(card);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

// Function to fetch and display the violation data as cards
function fetchReports(filter, searchinput) {
    let container = document.getElementById('card-container');
    container.innerHTML = '';
    let detailsUrl = null;
    if(searchinput == ''){
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/Reports`;
        } else if (filter == 'No action.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/NoactionReports`;
        } else if (filter == 'Forward to captain.'){
            console.log(filter);
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/CaptainReports`;
        } else if (filter == 'Forward to cityhall.'){
            console.log(filter);
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/CityReports`;
        } else if (filter == 'Resolved.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/ResolvedReports`;
        }
    } else {
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/Reports?search=${searchinput}`;
        } else if (filter == 'No action.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/NoactionReports?search=${searchinput}`;
        } else if (filter == 'Forward to captain.'){
            console.log(filter);
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/CaptainReports?search=${searchinput}`;
        } else if (filter == 'Forward to cityhall.'){
            console.log(filter);
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/CityReports?search=${searchinput}`;
        } else if (filter == 'Resolved.'){
            detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concernslist/ResolvedReports?search=${searchinput}`;
        }
    }

    // Fetch data from the API endpoint
    fetch(detailsUrl)
        .then(response => response.json())
        .then(data => {
            container = document.getElementById('card-container');
            container.innerHTML = '';
            data.forEach(report => {
                const card = document.createElement('div');
                card.classList.add('card');
                card.classList.add('shadow-sm');
                card.classList.add('border-light');
                
                // Adding the content of the card dynamically
                card.innerHTML = `
                <div class="card-body">
                <h5 class="card-title">${report.store_name}</h5>
                <h6 class="card-subtitle mb-2 text-muted">${report.store_address}</h6>
                <p><strong>Report ID:</strong> ${report.report_id}</p>
                <p><strong>Concern ID:</strong> ${report.concern_id}</p>
                <p><strong>Report Status:</strong> ${report.report_status}</p>
                <p><strong>Concerned Staff:</strong> ${report.concerned_staff}</p>
                <p><strong>Report Details:</strong></p>
                <p>${report.report_details}</p>
                </div>
                `;

                // Append the card to the container
                container.appendChild(card);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
}

fetchViolations(searchinput);