let currentcategory = 'category1';
let allStoreviolations = [];
let allStorelist = [];
let censusData = [];
let filter = 'has records.';
let searchinput = '';

//event listeners
document.getElementById('category').addEventListener('change', function(){
    currentcategory = document.getElementById('category').value;
    if (currentcategory == 'category1'){
        document.getElementById('category1-filter').style.display = 'block';
        document.getElementById('category2-filter').style.display = 'none';
        document.getElementById('category3-filter').style.display = 'none';
        document.getElementById('category4-filter').style.display = 'none';
        document.getElementById('listContainer').style.display = 'none';
        document.getElementById('printtracking').style.display = 'block';
        document.getElementById('card-container').style.display = 'block';
        document.getElementById('concernstatus').value = 'none.';
        document.getElementById('reportfilter').value = 'none.';
        document.getElementById('recordstatus').value = 'has records.';
        filter = 'has records.';
        fetchViolations(filter);
    } else if (currentcategory == 'category2'){
        document.getElementById('category2-filter').style.display = 'block';
        document.getElementById('category3-filter').style.display = 'none';
        document.getElementById('category4-filter').style.display = 'none';
        document.getElementById('category1-filter').style.display = 'none';
        document.getElementById('printtracking').style.display = 'none';
        document.getElementById('listContainer').style.display = 'none';
        document.getElementById('card-container').style.display = 'block';
        document.getElementById('violationcategory').value = 'has records.';
        document.getElementById('reportfilter').value = 'none.';
        document.getElementById('recordstatus').value = 'none.';
        filter = 'none.';
        fetchConcerns(filter, searchinput);
    } else if (currentcategory == 'category3'){
        document.getElementById('category3-filter').style.display = 'block';
        document.getElementById('category4-filter').style.display = 'none';
        document.getElementById('category1-filter').style.display = 'none';
        document.getElementById('category2-filter').style.display = 'none';
        document.getElementById('printtracking').style.display = 'none';
        document.getElementById('listContainer').style.display = 'none';
        document.getElementById('card-container').style.display = 'block';
        document.getElementById('violationcategory').value = 'has records.';
        document.getElementById('concernstatus').value = 'none.';
        document.getElementById('recordstatus').value = 'none.';
        filter = 'none.';
        fetchReports(filter, searchinput);
    } else if (currentcategory == 'category4'){
        document.getElementById('category4-filter').style.display = 'block';
        document.getElementById('category1-filter').style.display = 'none';
        document.getElementById('category2-filter').style.display = 'none';
        document.getElementById('category3-filter').style.display = 'none';
        document.getElementById('printtracking').style.display = 'none';
        document.getElementById('listContainer').style.display = 'none';
        document.getElementById('violationcategory').value = 'has records.';
        document.getElementById('concernstatus').value = 'none.';
        document.getElementById('reportfilter').value = 'none.';
        filter = 'has records.';
        fetchStorelist(filter, searchinput);
    }  else if (currentcategory == 'category5'){
        document.getElementById('category4-filter').style.display = 'none';
        document.getElementById('category1-filter').style.display = 'none';
        document.getElementById('category2-filter').style.display = 'none';
        document.getElementById('category3-filter').style.display = 'none';
        document.getElementById('printtracking').style.display = 'none';
        document.getElementById('card-container').style.display = 'none';
        document.getElementById('listContainer').style.display = 'block';
        document.getElementById('violationcategory').value = 'has records.';
        document.getElementById('concernstatus').value = 'none.';
        document.getElementById('reportfilter').value = 'none.';
        fetchCensuslist(searchinput);
    }

});

document.getElementById('violationcategory').addEventListener('change', function(){
    filter = document.getElementById('violationcategory').value;
    fetchViolations(filter);
});

document.getElementById('concernstatus').addEventListener('change', function(){
    filter = document.getElementById('concernstatus').value;
    fetchConcerns(filter, searchinput);
});


document.getElementById('reportfilter').addEventListener('change', function(){
    filter = document.getElementById('reportfilter').value;
    fetchReports(filter, searchinput);
});

document.getElementById('recordstatus').addEventListener('change', function(){
    filter = document.getElementById('recordstatus').value;
    fetchStorelist(filter, searchinput);
});

document.getElementById('recordstatus').addEventListener('change', function(){
    fetchCensuslist(searchinput);
});

document.getElementById('searchinput').addEventListener('keyup', function(){
    searchinput = document.getElementById('searchinput').value;
    console.log(currentcategory);
   if (currentcategory == 'category1'){
    console.log(currentcategory);
    searchViolations(searchinput);
   } else if (currentcategory == 'category2'){
    console.log(currentcategory);
    fetchConcerns(filter, searchinput);
   } else if (currentcategory == 'category3'){
    console.log(currentcategory);
    fetchReports(filter, searchinput);
   } else if (currentcategory == 'category4'){
    console.log(currentcategory);
    searchStorelist(filter, searchinput);
   } else if (currentcategory == 'category5'){
    console.log(currentcategory);
    searchCensuslist(searchinput);
   }
});

/********************* Store Violations ************************/
// Function to fetch and display the violation data as cards
function fetchViolations(filter) {
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    let container = document.getElementById('card-container');
    container.innerHTML = '';

    let detailsUrl = null;
    if (filter == 'has records.'){
        // Example URL for fetching detailed information
        detailsUrl = `http://localhost:8001/api/service/integration/registeredStoreViolations`;
    } else if (filter == 'no records.'){
        detailsUrl = `http://localhost:8001/api/service/integration/notRegisteredStoreViolations`;
    }

    fetch(detailsUrl)
        .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
        })
        .then(data => {
        allStoreviolations = data; // save full dataset
        createCards(allStoreviolations);
        })
        .catch(error => {
        console.error('Fetch error:', error);
        })
        .finally(() => {
        // Hide loading
        document.getElementById('loading-indicator').style.display = 'none';
        });
}
      
function createCards(data) {
const container = document.getElementById('card-container');
container.innerHTML = '';

if (data.length === 0) {
    container.innerHTML = `<p class="text-center">No stores found.</p>`;
    return;
}

data.forEach(store => {
    const card = document.createElement('div');

    const violations = store.violation_counts;
    let violationsHTML = '';

    for (const [key, value] of Object.entries(violations)) {
    if (value > 0) {
        violationsHTML += `<span class="badge bg-danger mb-1">${key}: ${value}</span><br>`;
    }
    }

    if (!violationsHTML) {
    violationsHTML = `<span class="badge bg-success">No Violations</span>`;
    }

    card.innerHTML = `
    <div class="card p-3">
        <div class="card-body d-flex flex-column">
        <h5 class="card-title">${store.business_name}</h5>
        <h6 class="card-subtitle mb-2 text-muted">Store ID: ${store.store_id}</h6>
        <h6 class="card-subtitle mb-2 text-muted">Store Address: ${store.business_address}</h6>
        <hr>
        <div class="mt-auto">${violationsHTML}</div>
        </div>
    </div>
    `;

    container.appendChild(card);
});
}

function searchViolations(searchinput){
    const filteredStores = allStoreviolations.filter(store =>
        store.business_name.includes(searchinput)
    );
    createCards(filteredStores);
}

// Function to fetch and display the concerns data as cards
function fetchConcerns(filter, searchinput) {
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    let container = document.getElementById('card-container');
    container.innerHTML = '';
    let detailsUrl = null;
    if(searchinput == ''){
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `http://localhost:8001/api/service/concerns/Concerns`;
        } else if (filter == 'No action.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/NoactionConcerns`;
        } else if (filter == 'Reported.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/ReportedConcerns`;
        } else if (filter == 'Resolved.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/ResolvedConcerns`;
        }  else if (filter == 'Not valid.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/notValidConcerns`;
        }
    } else {
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `http://localhost:8001/api/service/concerns/Concerns?search=${searchinput}`;
        } else if (filter == 'No action.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/NoactionConcerns?search=${searchinput}`;
        } else if (filter == 'Reported.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/ReportedConcerns?search=${searchinput}`;
        } else if (filter == 'Resolved.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/ResolvedConcerns?search=${searchinput}`;
        } else if (filter == 'Not valid.'){
            detailsUrl = `http://localhost:8001/api/service/concerns/notValidConcerns?search=${searchinput}`;
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
                
                let citizenName = '';
                let residentType = '';
                if(report.anonymity_status == 'non-anonymous'){
                    citizenName = report.fullname;
                    residentType = report.resident_type;
                } else if(report.anonymity_status == 'anonymous'){
                    citizenName = "Anonymous.";
                    residentType = "Unknown."
                }

                // Adding the content of the card dynamically
                card.innerHTML = `
                <div class="card-body">
                    <h5 class="card-title">${report.store_name}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">${report.store_address}</h6>
                    <p><strong>Concerned Citizen:</strong> ${citizenName}</p>
                    <p><strong>Resident Type:</strong> ${residentType}</p>
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
        .catch(error => console.error('Error fetching data:', error))
        .finally(() => {
            // Hide loading
            document.getElementById('loading-indicator').style.display = 'none';
        });
}

// Function to fetch and display the violation data as cards
function fetchReports(filter, searchinput) {
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    let container = document.getElementById('card-container');
    container.innerHTML = '';
    let detailsUrl = null;
    if(searchinput == ''){
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `http://localhost:8001/api/service/reports/Reports`;
        } else if (filter == 'createdLowRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/createdlowrisk`;
        } else if (filter == 'createdMediumRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/createdmediumrisk`;
        } else if (filter == 'createdHighRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/createdhighrisk`;
        } else if (filter == 'resolvedLowRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedlowrisk`;
        } else if (filter == 'resolvedMediumRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedmediumrisk`;
        } else if (filter == 'resolvedHighRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedhighrisk`;
        } else if (filter == 'actionLowRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/actionlowrisk`;
        } else if (filter == 'actionMediumRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/actionmediumrisk`;
        } else if (filter == 'actionHighRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/actionhighrisk`;
        }
    } else {
        if (filter == 'none.'){
        // Example URL for fetching detailed information
        detailsUrl = `http://localhost:8001/api/service/reports/Reports?search=${searchinput}`;
        } else if (filter == 'createdLowRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/createdlowrisk?search=${searchinput}`;
        } else if (filter == 'createdMediumRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/createdmediumrisk?search=${searchinput}`;
        } else if (filter == 'createdHighRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/createdhighrisk?search=${searchinput}`;
        } else if (filter == 'resolvedLowRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedlowrisk?search=${searchinput}`;
        } else if (filter == 'resolvedMediumRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedmediumrisk?search=${searchinput}`;
        } else if (filter == 'resolvedHighRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/resolvedhighrisk?search=${searchinput}`;
        } else if (filter == 'actionLowRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/actionlowrisk?search=${searchinput}`;
        } else if (filter == 'actionMediumRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/actionmediumrisk?search=${searchinput}`;
        } else if (filter == 'actionHighRisk.'){
            detailsUrl = `http://localhost:8001/api/service/reports/actionhighrisk?search=${searchinput}`;
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
                
                if(filter == 'none.' || filter == 'createdLowRisk.' || filter == 'createdMediumRisk.'|| filter == 'createdHighRisk.' || filter == 'resolvedLowRisk.' ||
                    filter == 'resolvedMediumRisk.' || filter == 'resolvedHighRisk.') {

                    // Adding the content of the card dynamically
                    card.innerHTML = `
                    <div class="card-body">
                    <h5 class="card-title">${report.store_name}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">${report.store_address}</h6>
                    <p><strong>Report ID:</strong> ${report.report_id}</p>
                    <p><strong>Report for Concern ID:</strong> ${report.concern_id}</p>
                    <p><strong>Report created:</strong> ${report.create_at}</p>
                    <p><strong>Report category:</strong> ${report.report_category}</p>
                    <p><strong>Report Status:</strong> ${report.report_status}</p>
                    <p><strong>Report Details:</strong></p>
                    <p>${report.report_details}</p>
                    <button type="button" id="printreport" class="btn btn-success rounded-3 print-report-btn">Print</button>
                    </div>`;

                    // Add event listener to the button inside this card
                    const printreport = card.querySelector('.print-report-btn');
                    printreport.addEventListener('click', function() {
                    // Your print logic here
                    updateReportContent(report.report_id);
                    openReportModal();
                    });

                } else if (filter == 'actionLowRisk.' || filter == 'actionMediumRisk.' || filter == 'actionHighRisk.') {

                    // Adding the content of the card dynamically
                    card.innerHTML = `
                    <div class="card-body">
                    <h5 class="card-title">${report.store_name}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">${report.store_address}</h6>
                    <p><strong>Action ID:</strong> ${report.action_id}</p>
                    <p><strong>Action for Report ID:</strong> ${report.report_id}</p>
                    <p><strong>Action conducted:</strong> ${report.create_at}</p>
                    <p><strong>Action details:</strong> ${report.actions}</p>
                    <p><strong>Staff:</strong> ${report.staff}</p>
                    <button type="button" id="printaction" class="btn btn-success rounded-3 print-action-btn">Print</button>
                    </div>
                    `;

                    // Add event listener to the button inside this card
                    const printaction = card.querySelector('.print-action-btn');
                    printaction.addEventListener('click', function() {
                    // Your print logic here
                    updateActionContent(report.action_id);
                    openActionModal();
                    });
                }


                // Append the card to the container
                container.appendChild(card);
            });
        })
        .catch(error => console.error('Error fetching data:', error))
        .finally(() => {
            // Hide loading
            document.getElementById('loading-indicator').style.display = 'none';
        });
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

/********************* Store List ************************/
// Function to fetch and display the store data as cards
function fetchStorelist(filter) {
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    let container = document.getElementById('card-container');
    container.innerHTML = '';

    let detailsUrl = null;
    if (filter == 'has records.'){

    fetch('http://localhost:8001/api/service/integration/business')
        .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
        })
        .then(data => {
            allStorelist = data;
            createHasrecords(data);
        })
        .catch(error => {
        console.error('Fetch error:', error);
        })
        .finally(() => {
        // Hide loading
        document.getElementById('loading-indicator').style.display = 'none';
        });
    } else if(filter == 'no records.'){

    fetch('http://localhost:8001/api/service/concerns/stores')
        .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
        })
        .then(data => {
        allStorelist = data; // save full dataset
        createNorecords(data);
        })
        .catch(error => {
        console.error('Fetch error:', error);
        })
        .finally(() => {
        // Hide loading
        document.getElementById('loading-indicator').style.display = 'none';
        });
    }
}

    // Function to render cards
    function createHasrecords(data) {
        const container = document.getElementById('card-container');
        container.innerHTML = '';

        if (data.length === 0) {
        container.innerHTML = `<div class="text-muted text-center">No results found.</div>`;
        return;
        }

        data.forEach(item => {
        const col = document.createElement('div');

        col.innerHTML = `
            <div class="card shadow-sm h-100">
            <div>
                <h5>ID: ${item.id} | ${item.business_name}</h5><br>
                <h6 class="text-muted">${item.business_address}</h6>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> ${item.email}</p>
                <p><strong>Business Type:</strong> ${item.business_type}</p>
                <p><strong>Date Application:</strong> ${item.date_application}</p>
                <p><strong>Expiration Date:</strong> ${item.expiration_date ?? 'N/A'}</p>
                <p><strong>Document Status:</strong> ${item.document_status || 'N/A'}</p>
                <p><strong>Display Status:</strong> ${item.display_status}</p>
                <p><strong>Source Table:</strong> ${item.source_table}</p>
            </div>
            </div>
        `;
        container.appendChild(col);
        });
    }
    // Function to render cards
    function createNorecords(data) {
        const container = document.getElementById('card-container');
        container.innerHTML = '';

        if (data.length === 0) {
        container.innerHTML = `<div class="text-muted text-center">No results found.</div>`;
        return;
        }

        data.forEach(item => {
        const col = document.createElement('div');

        col.innerHTML = `
            <div class="card shadow-sm h-100">
            <div>
                <h5>ID: ${item.store_id} | ${item.store_name}</h5><br>
                <h6 class="text-muted">${item.store_address}</h6>
            </div>
            <div class="card-body">
                <p><strong>Record status:</strong> ${item.record_status}</p>
            </div>
            </div>
        `;
        container.appendChild(col);
        });
    }

    function searchStorelist(filter, searchinput){
        if(filter == 'has records.'){
            const filteredData = allStorelist.filter(item => 
                item.business_name.includes(searchinput)
            );
            createHasrecords(filteredData);
        } else if (filter == 'no records.'){
            const filteredData = allStorelist.filter(item => 
                item.store_name.includes(searchinput)
            ); 
            createNorecords(filteredData);
        }
    }

/********************* Census List ************************/
const listContainer = document.getElementById('listContainer');
const modal = document.getElementById('detailsModal');
const closeModalBtn = document.getElementById('closeModal');
const modalContent = document.getElementById('modalContent');
// Function to fetch and display the store data as cards
function fetchCensuslist() {
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    fetch('https://barangay-api-s4rv.onrender.com/api/cencus')
    .then(response => {
      if (!response.ok) throw new Error('Network error');
      return response.json();
    })
    .then(data => {
      censusData = data;
      if (censusData.length === 0) {
        showMessage('No census data available.');
      } else {
        //message.style.display = 'none';
        displayList(censusData);
      }
    })
    .catch(error => {
      console.error('Fetch error:', error);
      //showMessage('Failed to load data.');
    })
    .finally(() => {
    // Hide loading
    document.getElementById('loading-indicator').style.display = 'none';
    });
}

    // Function to render cards
    function displayList(dataArray) {
        listContainer.innerHTML = '';
        if (dataArray.length === 0) {
          return;
        }
  
        dataArray.forEach((person, index) => {
          const item = document.createElement('div');
          item.className = 'list-item';
  
          item.innerHTML = `
            <div class="list-info">
              <p><span>${person.firstname} ${person.lastname}</span> - ${person.barangay}</p>
            </div>
            <button class="btn btn-success" onclick="showDetails(${index})">View Details</button>
          `;
          listContainer.appendChild(item);
        });
      }

    function searchCensuslist(searchinput){
      const query = searchinput.toLowerCase();
      const filtered = censusData.filter(person =>
        person.firstname.toLowerCase().includes(query) ||
        person.lastname.toLowerCase().includes(query) ||
        person.barangay.toLowerCase().includes(query)
      );
      displayList(filtered);
    }

    function showDetails(index) {
        const person = censusData[index];
        modalContent.innerHTML = `
          <h2>${person.firstname} ${person.middlename} ${person.lastname}</h2>
          <p><span class="label">Firstname:</span> ${person.firstname}</p>
          <p><span class="label">Middlename:</span> ${person.middlename}</p>
          <p><span class="label">Lastname:</span> ${person.lastname}</p>
          <p><span class="label">Date of Census:</span> ${person.dateofcencus}</p>
          <p><span class="label">Area of Census Street:</span> ${person.areaofcencusstreet}</p>
          <p><span class="label">Birthday:</span> ${person.birthday}</p>
          <p><span class="label">Age:</span> ${person.age}</p>
          <p><span class="label">Gender:</span> ${person.gender}</p>
          <p><span class="label">Civil Status:</span> ${person.civilstatus}</p>
          <p><span class="label">Current School Enrollment:</span> ${person.currentschoolenrollment}</p>
          <p><span class="label">Educational Attainment:</span> ${person.educationalattainment}</p>
          <p><span class="label">Employment Status:</span> ${person.employmentstatus}</p>
          <p><span class="label">Occupation:</span> ${person.occupation}</p>
          <p><span class="label">House Number:</span> ${person.housenumber}</p>
          <p><span class="label">Street Name:</span> ${person.streetname}</p>
          <p><span class="label">Barangay:</span> ${person.barangay}</p>
          <p><span class="label">City:</span> ${person.city}</p>
          <p><span class="label">Province:</span> ${person.province}</p>
          <p><span class="label">House Type:</span> ${person.housetype}</p>
          <p><span class="label">Health Status:</span> ${person.healthstatus}</p>
          <p><span class="label">Disability Status:</span> ${person.disabilitystatus}</p>
          <p><span class="label">Existing Health Condition:</span> ${person.existinghealthcondition}</p>
          <p><span class="label">Fully Immunized:</span> ${person.fullyimmunized}</p>
          <p><span class="label">COVID-19 Vaccination:</span> ${person.covid19vaccination}</p>
          <p><span class="label">Housing Type:</span> ${person.housingtype}</p>
          <p><span class="label">Year Constructed:</span> ${person.yearofconstructed}</p>
          <p><span class="label">Resident Lived:</span> ${person.residentlived}</p>
          <p><span class="label">Mobile Number:</span> ${person.mobilenumber}</p>
          <p><span class="label">Emergency Contact Name:</span> ${person.emergencycontactname}</p>
          <p><span class="label">Emergency Contact Number:</span> ${person.emergencycontactnumber}</p>
          <p><span class="label">Relationship to Emergency Contact:</span> ${person.relationshiptoemergencycontact}</p>
          <p><span class="label">Number of House Members:</span> ${person.numberofhousemembers}</p>
          <p><span class="label">Year Conducting:</span> ${person.yearconducting}</p>
          <p><span class="label">Citizenship:</span> ${person.citizenship}</p>
          <p><span class="label">Place of Birth:</span> ${person.placeofbirth}</p>
          <p><span class="label">Email Address:</span> ${person.emailadress}</p>
          <p><span class="label">Government ID:</span> ${person.governmentid}</p>
          <p><span class="label">Government ID Number:</span> ${person.governmentidnumber}</p>
          <p><span class="label">School Type:</span> ${person.schooltype}</p>
          <p><span class="label">Created At:</span> ${person.createdAt}</p>
          <p><span class="label">Updated At:</span> ${person.updatedAt}</p>
        `;
        modal.style.display = 'block';
      }

      closeModalBtn.onclick = () => {
        modal.style.display = 'none';
      }
  
      window.onclick = event => {
        if (event.target == modal) modal.style.display = 'none';
      }

    /****************** Print tracking **************/
    const printtracking = document.getElementById("printtracking");
    printtracking.setAttribute("data-bs-toggle", "modal");
    printtracking.setAttribute("data-bs-target", "#selectdateModal");
    //event listeners
    document.getElementById('printtracking').addEventListener('click', function(){
        selectdateModal.click();
    });

    document.getElementById('selectType').addEventListener('click', function(){
        showInput();
    });

    document.getElementById('selectSubmit').addEventListener('click', function(){
        submitForm();
    });

    //selection month or date
    function showInput() {
        document.getElementById('selectErr').innerHTML = '';
        document.getElementById('monthErr').innerHTML = '';
        document.getElementById('dateErr').innerHTML = '';
        const selectType = document.getElementById("selectType").value;
        const monthInput = document.getElementById("monthInput");
        const dateInput = document.getElementById("dateInput");
  
        monthInput.style.display = "none";
        dateInput.style.display = "none";
  
        if (selectType === "month") {
          monthInput.style.display = "block";
        } else if (selectType === "date") {
          dateInput.style.display = "block";
        }
      }
  
      function submitForm() {
        let isValid = true;
        let selectedValue = "";
        const selectType = document.getElementById("selectType").value;
        const monthValue = document.getElementById("monthPicker").value;
        const dateValue = document.getElementById("datePicker").value;
        document.getElementById('selectErr').innerHTML = '';
        document.getElementById('monthErr').innerHTML = '';
        document.getElementById('dateErr').innerHTML = '';
  
        if (selectType === "") {
            document.getElementById('selectErr').innerHTML = 'Please select a type first.';
            isValid = false;
        }
        
        // Validate and assign selected value
        if (selectType === "month") {
            if (monthValue === "") {
            document.getElementById('monthErr').innerHTML = 'Please select month.';
            isValid = false;
            } else {
            selectedValue = monthValue;
            }
        }
        
        if (selectType === "date") {
            if (dateValue === "") {
            document.getElementById('dateErr').innerHTML = 'Please select date.';
            isValid = false;
            } else {
            selectedValue = dateValue;
            }
        }

        if (selectType === "allrecords") {
            selectedValue = "";
        }

        if(isValid){
           const closeselectdateModal = document.getElementById('closeselectdateModal');
           const selectForm = document.getElementById('selectForm');
           const monthInput = document.getElementById("monthInput");
           const dateInput = document.getElementById("dateInput");
           closeselectdateModal.click();
           selectForm.reset();
           monthInput.style.display = "none";
           dateInput.style.display = "none";
           fetchStoreViolations(filter, selectedValue);
        }
    }

    function fetchStoreViolations(filter, selectedValue){
        let detailsUrl = null;
        if(selectedValue == ''){
            if (filter == 'has records.'){
                // Example URL for fetching detailed information
                detailsUrl = `http://localhost:8001/api/service/integration/registeredStoreViolations`;
            } else if (filter == 'no records.'){
                detailsUrl = `http://localhost:8001/api/service/integration/notRegisteredStoreViolations`;
            }
        } else {
            if (filter == 'has records.'){
                // Example URL for fetching detailed information
                detailsUrl = `http://localhost:8001/api/service/integration/registeredStoreViolations?search=${selectedValue}`;
            } else if (filter == 'no records.'){
                detailsUrl = `http://localhost:8001/api/service/integration/notRegisteredStoreViolations?search=${selectedValue}`;
            }
        }
    
        fetch(detailsUrl)
            .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
            })
            .then(data => {
            openTrackingModal();
            updateViolationContent(data, filter, selectedValue);
            })
            .catch(error => {
            console.error('Fetch error:', error);
            })
            .finally(() => {
            // Hide loading
            document.getElementById('loading-indicator').style.display = 'none';
            });
    }

    function createViolationsPage(storeType, formattedDate) {
        const newPage = document.createElement('div');
        newPage.classList.add('report-violations');
        newPage.innerHTML = `
          <div id="reportHeader">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
              <div style="flex: 0 0 80px; text-align: center;">
                <img src="assets/images/oldcapitolsite.jpg" alt="Left Logo" style="width: 100px; height: auto; margin-left: 5px;">
              </div>
              <div style="flex: 1; text-align: center;">
                <p style="margin: 0px;"><span style="font-family: Sans Serif Collection; font-size: 16px; letter-spacing: 2px;">Republic of the Philippines</span></p>
                <p style="margin: 0px;"><strong>OLD CAPITOL SITE</strong></p>
                <p style="margin: 0px;">Quezon City</p>
                <p style="margin: 0px;">Masaya Interior corner Matiwasay St.</p>
                <p style="margin: 0px;">oldcapitolsitemercado@gmail.com</p>
              </div>
              <div style="flex: 0 0 80px; text-align: center;">
                <img src="assets/images/quezoncity.png" alt="Right Logo" style="width: 100px; height: auto;">
              </div>
            </div>
            <hr style="height:2px; background-color:#000; border:none; margin:0;">
            <hr style="height:2px; background-color:#000; border:none; margin:2px 0 44px 0;">
            <div><p style="text-align: center; font-size:27px; letter-spacing: 10px;">STORE VIOLATIONS REPORT</p></div>
            <p id="storeType" style="text-align:center;"><span>${storeType}</span></p>
            <p id="reportDate" style="text-align:center;"><span>Date: ${formattedDate}</span></p>
          </div>
          <div class="table-con">
            <table class="fr-table-selection-hover" style="border-collapse: collapse; border: none; width: 1035px;">
              <thead>
                <tr>
                  <th style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">Store ID</th>
                  <th style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">Store Name</th>
                  <th style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">Store Address</th>
                  <th style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">Violations</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        `;
        document.getElementById('additionalPages').appendChild(newPage);
        return newPage;
      }
  
      function updateViolationContent(storeData, filter, selectedValue) {
        const tableBody = document.getElementById('store-table-body');
        const additionalPages = document.getElementById('additionalPages');
        additionalPages.innerHTML = '';
        tableBody.innerHTML = ''; // Clear existing rows
        let currentPage = document.getElementById('masterReportPage');
        let accumulatedHeight = 0;  // Track accumulated height of rows
        let storeType = '';

        if (filter == 'has records.'){
            storeType = 'Registered stores';
        } else if (filter == 'no records.'){
            storeType = 'Non registered stores';
        }

        function formatDate(selectedValue) {
            let dateObj;
            let options;
          
            if (selectedValue.length === 10) {
              // full date (YYYY-MM-DD)
              dateObj = new Date(selectedValue);
              options = { year: 'numeric', month: 'long', day: 'numeric' };
              return dateObj.toLocaleDateString('en-US', options);
            } else if (selectedValue.length === 7) {
              // year-month (YYYY-MM)
              dateObj = new Date(selectedValue + '-01');
              options = { month: 'long' };
              let month = dateObj.toLocaleDateString('en-US', options);
              let year = dateObj.getFullYear();
              return `${month}, ${year}`;
            } else {
              return 'Invalid Date';
            }
          }

          let formattedDate = formatDate(selectedValue);
          

        document.getElementById('storeType').innerHTML = `<span>${storeType}</span>`;
        document.getElementById('reportDate').innerHTML = `<span>Date: ${formattedDate}</span>`;

        storeData.forEach((store, index) => {
          // Prepare violation string
          let violations = 'None';
          if (store.violation_counts && Object.keys(store.violation_counts).length > 0) {
            violations = Object.entries(store.violation_counts).map(
              ([key, val]) => `${key}: ${val}`
            ).join('<br>');
          }
  
          let row = document.createElement('tr');
          row.innerHTML = `
            <td style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">${store.store_id}</td>
            <td style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">${store.business_name}</td>
            <td style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">${store.business_address}</td>
            <td style="border: 1px solid #000000;padding: 10px;text-align: left;vertical-align: top;">${violations}</td>
          `;
          
          // Append row to the current page
          currentPage.querySelector('tbody').appendChild(row);

          // Calculate row height
          const rowHeight = row.offsetHeight;
          accumulatedHeight += rowHeight;

          const pageMaxHeight = 325; // Define the max height for a page
  
          // If accumulated height exceeds the page height, create a new page and reset
          if (accumulatedHeight > pageMaxHeight) {
            accumulatedHeight = 0;
            currentPage = createViolationsPage(storeType, formattedDate);
            currentPage.querySelector('tbody').appendChild(row);  // Add the row to the new page
          }
        });
      }

    function openTrackingModal() {
        document.getElementById("trackingmodal").style.display = "block";
        document.body.classList.add('modal-open'); // Disable body scrolling
    }
      
    function closeTrackingModal() {
    document.getElementById("trackingmodal").style.display = "none";
    document.body.classList.remove('modal-open'); // Re-enable body scrolling
    //window.location.reload();
    }

    function alertTracking(filter){
        if (filter == 'has records.'){
            alert('Tracking Print Registered.');
        } else if (filter == 'no records.'){
            alert('Tracking Print Not registered.');
        }
    }

    function printWithStyle(style) {
        // Disable both
        document.getElementById('printviolations').disabled = true;
        document.getElementById('printreports').disabled = true;
    
        // Enable the selected one
        if (style === 'landscape') {
          document.getElementById('printviolations').disabled = false;
        } else {
          document.getElementById('printreports').disabled = false;
        }
    
        // Wait a short time before printing
        setTimeout(() => {
          window.print();
        }, 100);
      }


fetchViolations(filter);