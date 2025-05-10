let currentcategory = 'pending';
let searchinput = '';

//event listeners
document.getElementById('reportStatus').addEventListener('change', function(){
    const reportStatus = document.getElementById('reportStatus').value;
    if(reportStatus == 'showPending'){
    currentcategory = 'pending';
    fetchTable(currentcategory, searchinput);
    } else if(reportStatus == 'showSent'){
        currentcategory = 'sent';
        fetchTable(currentcategory, searchinput);
    }
});

document.getElementById('searchTerm').addEventListener('keyup', function(){
    searchinput = document.getElementById('searchTerm').value;
   if (currentcategory == 'pending'){
    fetchTable(currentcategory, searchinput);
   } else if (currentcategory == 'sent'){
    fetchTable(currentcategory, searchinput);
   }
});

document.getElementById('clrupdateStatus').addEventListener('click', function(){
  Swal.fire({
    title: "Mark as sent?",
    text: "",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes"
    }).then((result) => {
        if (result.isConfirmed) {
          updateStatus();
        }
      });
});

function fetchTable(currentcategory, searchinput){
    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    tableBody.innerHTML = ''; // This will remove all the previous rows

    let detailsUrl = null;
    if(searchinput == ''){
        if(currentcategory == 'pending'){
            detailsUrl = `http://localhost:8001/api/service/safetymonitoring/clrngopsPending`;
        } else if(currentcategory == 'sent'){
            detailsUrl = `http://localhost:8001/api/service/safetymonitoring/clrngopsSent`;
        }
    }else {
        if(currentcategory == 'pending'){
            detailsUrl = `http://localhost:8001/api/service/safetymonitoring/clrngopsPending?search=${searchinput}`;
        } else if(currentcategory == 'sent'){
            detailsUrl = `http://localhost:8001/api/service/safetymonitoring/clrngopsSent?search=${searchinput}`;
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
                const yearCell = document.createElement("td");
                yearCell.textContent = item.year;
                row.appendChild(yearCell);

                const monthCell = document.createElement("td");
                monthCell.textContent = item.month;
                row.appendChild(monthCell);

                const statusCell = document.createElement("td");
                statusCell.textContent = item.status;
                row.appendChild(statusCell);

                const countCell = document.createElement("td");
                countCell.textContent = item.report_count;
                row.appendChild(countCell);

                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "View";
                button.classList.add("btn", "btn-success");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    openModal();
                    fetchItemDetails(currentcategory, item.year_month);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

function fetchItemDetails(currentcategory, year_month) {
  if(currentcategory == 'pending'){
  document.getElementById("clrupdateStatus").style.display = 'block';
  } else if(currentcategory == 'sent'){
  document.getElementById("clrupdateStatus").style.display = 'none';
  }

  document.querySelector(".yearmonth").textContent = year_month;

  tabledetailsUrl = `http://localhost:8001/api/service/safetymonitoring/reporttable/${year_month}`;
    // Fetch data from the API endpoint to populate the table
    fetch(tabledetailsUrl)
        .then(response => response.json())
        .then(data => {
          generatePages(data);
        })
        .catch(error => console.error('Error fetching data:', error));

    const rowsPerPage = 5;
    const originalTableBody = document.getElementById('tableBody');
    const additionalPages = document.getElementById('additionalPages');

    function createRow(entries) {
    let rows = '';

    // Group by operation_date
    const groupedByDate = entries.reduce((acc, curr) => {
      if (!acc[curr.operation_date]) acc[curr.operation_date] = [];
      acc[curr.operation_date].push(curr);
      return acc;
    }, {});

    // Loop through each group
    for (const [operation_date, group] of Object.entries(groupedByDate)) {
      const totalRows = group.length;

      group.forEach((entry, index) => {
        const isFirstRow = index === 0;

        rows += `<tr>`;

        if (isFirstRow) {
          const yesIcon = group.some(e => e.conducted === "Yes") ? "✅" : "";
          const noIcon = group.some(e => e.conducted === "No") ? "✅" : "";

          rows += `
            <td rowspan="${totalRows}">${yesIcon}</td>
            <td rowspan="${totalRows}">${noIcon}</td>
          `;
        }

        rows += `
          <td>${entry.street_name}</td>
          <td>${entry.road_length}</td>
        `;

        if (isFirstRow) {
          rows += `
            <td rowspan="${totalRows}">${operation_date}</td>
            <td rowspan="${totalRows}">${entry.action_taken}</td>
            <td rowspan="${totalRows}">${entry.remarks}</td>
          `;
        }

        rows += `</tr>`;
      });
    }

    return rows;
  }

  function generatePages(data) {
    const rowsPerPage = 5;
    const totalChunks = Math.ceil(data.length / rowsPerPage);
    const template = document.querySelector('.report-table'); // your template
    const additionalPages = document.getElementById('additionalPages'); // container for new pages

    // Get the first or any valid date (e.g., latest or earliest)
    const firstDate = new Date(data[0].operation_date);
    const month = firstDate.toLocaleString('en-US', { month: 'long' }).toUpperCase();
    const year = firstDate.getFullYear();

    const monthLabelDiv = document.getElementById('reportmonth');
    monthLabelDiv.innerHTML = `
    <p style="text-align: center; line-height: 1;">
      <span style="font-family:Cambria;font-size:12px;">
        <strong>For the Month of ${month} CY ${year}</strong>
      </span>
    </p>
    `;

    for (let i = 0; i < totalChunks; i++) {
      const chunk = data.slice(i * rowsPerPage, (i + 1) * rowsPerPage);

      // Clone the base report container
      const newPage = template.cloneNode(true);
      newPage.classList.add('report-table');

      // Replace table body content
      const currentPageTableBody = newPage.querySelector('#tableBody');
      currentPageTableBody.innerHTML = createRow(chunk);

      // Append certification block only on the last page
      if (i === totalChunks - 1) {
        const certDiv = document.createElement('div');
        certDiv.innerHTML = `
          <div style="font-family:Cambria; font-style:italic; font-size:12px; margin-top:20px;">
            <p style="text-align:left; margin: 0 0 0 396pt;">I hereby CERTIFY and DECLARE that the above information and activities are true</p>
            <p style="text-align:left; margin: 0 0 0 396pt;">And accurate to the BEST of my knowledge.</p>
          
            <p style="margin-top:15px; text-align:left; margin-left:72pt; font-size:16px;">Prepared by:</p>
          
            <div style="margin-top:30px; margin-left:72pt;">
              <p style="font-size:19px; font-weight:bold; font-style:italic; margin:0;">Ms. Geraldine Rosales</p>
              <p style="font-size:16px; text-indent:18pt; margin:0;">Barangay Secretary</p>
            </div>
          </div>
        `;
        newPage.appendChild(certDiv);
      }

      additionalPages.appendChild(newPage);
    }

    // Optionally hide the original template
    template.style.display = "none";
  }

  const streetdetailsUrl = `http://localhost:8001/api/service/safetymonitoring/reportstreets/${year_month}`;
  
    fetch(streetdetailsUrl)
      .then(response => response.json())
      .then(data => {
        createstreetpage(data);
      })
      .catch(error => console.error('Error fetching data:', error));
  
    function createstreetpage(streetData) {
      // Handle the first street separately
      const firstStreet = streetData[0];
      const firstDate = new Date(firstStreet.operation_date);
      const firstMonth = firstDate.toLocaleString('en-US', { month: 'long' }).toUpperCase();
      const firstYear = firstDate.getFullYear();
      const firstFullDate = firstDate.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }).toUpperCase();
  
      // Update header month and date
      const monthLabelDiv = document.getElementById('reportmonthstreet');
      monthLabelDiv.innerHTML = `
        <p style="text-align: center;margin:0px;">
          <span style="font-family:Cambria;font-size:12px;">
            <strong>For the Month of ${firstMonth} CY ${firstYear}</strong>
          </span>
        </p>
      `;
  
      const dateLabelDiv = document.getElementById('reportdatestreet');
      dateLabelDiv.innerHTML = `
        <p style="text-align: center;margin:0px;">
          <span style="font-family:Cambria;font-size:12px;">
            <strong>Date of BaRCO conducted: ${firstFullDate}</strong>
          </span>
        </p>
      `;
  
      // Populate the first report-streets with first street data
      const reportDiv = document.querySelector(".report-streets");
  
      const streetNameText = reportDiv.querySelector("em strong");
      streetNameText.textContent = `${firstStreet.street_name.toUpperCase()},`;
  
      const imageTags = reportDiv.querySelectorAll("img[src='imagepath']");
      if (imageTags.length === 3) {
        imageTags[0].src = `${firstStreet.file_path1}`;
        imageTags[1].src = `${firstStreet.file_path2}`;
        imageTags[2].src = `${firstStreet.file_path3}`;
      }
  
      // Generate pages for the rest of the streets
      const container = document.getElementById("additionalStreets");
  
      for (let i = 1; i < streetData.length; i++) {
        const street = streetData[i];
        const opDate = new Date(street.operation_date);
        const month = opDate.toLocaleString('en-US', { month: 'long' }).toUpperCase();
        const year = opDate.getFullYear();
        const fullDate = opDate.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }).toUpperCase();
  
        const page = document.createElement("div");
        page.className = "report-streets page-break";
        page.innerHTML = `
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 48px;">
            <!-- Left Image -->
            <div style="flex: 0 0 80px; text-align: center;">
              <img src="assets/images/oldcapitolsite.jpg" alt="Left Logo" style="width: 135px; height: auto; margin-left: 150px;">
            </div>
  
            <!-- Center Text -->
            <div style="flex: 1; text-align: center;">
              <p style="margin: 0px; line-height: 1;"><span style="font-family: Sans Serif Collection; font-size: 16px;"><strong>REPUBLIKA NG PILIPINAS</strong></span></p>
              <p style="margin: 0px; line-height: 1;"><span style="font-family: Sans Serif Collection; font-size: 16px;"><strong><span style="color: rgb(41, 105, 176);">LUNGSOD NG QUEZON</span></strong></span></p>
              <p style="margin: 0px; line-height: 1;"><span style="font-family: Arial; font-size: 29px; font-weight: normal;"><strong>BARANGAY OLD CAPITOL SITE</strong></span></p>
              <p style="margin: 0px; line-height: 1;"><span style="font-family: Arial; font-size: 17px;"><strong>Tanggapan ng Sangguniang Barangay</strong></span></p>
              <p style="margin: 0px; line-height: 1;"><span style="font-family: Arial; font-size: 12px;"><strong>Masaya Interior corner Matiwasay St. Old Capitol Site</strong></span></p>
              <p style="margin: 0px; line-height: 1;"><span style="font-family: Arial; font-size: 12px;"><strong>Dist IV, Quezon City</strong></span></p>
            </div>
  
            <!-- Right Images -->
            <div style="flex: 0 0 80px; text-align: center;">
              <img src="assets/images/bagongpilipinas.png" alt="Right Logo" style="width: 135px; height: auto; margin-right: 25px;">
            </div>
  
            <div style="flex: 0 0 80px; text-align: center;">
              <img src="assets/images/quezoncity.png" alt="Right Logo" style="width: 135px; height: auto;">
            </div>
          </div>
  
          <div>
            <p style="text-align: center;margin:0px;"><span style="font-family:Cambria;font-size:12px;"><strong>Conduct of the Barangay Road Clearing Operations (BaRCO)</strong></span></p>
            <p style="text-align: center;margin:0px;"><span style="font-family:Cambria;font-size:12px;"><strong>For the Month of ${month} CY ${year}</strong></span></p>
            <p style="text-align: center;margin:0px;"><span style="font-family:Cambria;font-size:12px;"><strong>Date of BaRCO conducted: ${fullDate}</strong></span></p>
          </div>
  
          <div>
            <p style="margin-left:0pt;text-indent:18pt;text-align:left;">
              <em><span style="font-family:Cambria;font-style:italic;font-size:16px;"><strong>${street.street_name.toUpperCase()},</strong></span></em>
            </p>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
              <img src="${street.file_path1}" style="width: 9cm; height: 11.5cm; object-fit: cover;" />
              <img src="${street.file_path2}" style="width: 9cm; height: 11.5cm; object-fit: cover;" />
              <img src="${street.file_path3}" style="width: 9cm; height: 11.5cm; object-fit: cover;" />
            </div>
          </div>
        `;
  
        container.appendChild(page);
      }
    }

    const summarydetailsUrl = `http://localhost:8001/api/service/safetymonitoring/reportinfo/${year_month}`;
  
    fetch(summarydetailsUrl)
      .then(response => response.json())
      .then(data => {
        createsummarypage(data);
      })
      .catch(error => console.error('Error fetching data:', error));
    
      function createsummarypage(barcoSummaryData){
      // Handle the first entry
      const firstEntry = barcoSummaryData[0];
      const firstDate = new Date(firstEntry.operation_date);
      const firstMonth = firstDate.toLocaleString('en-US', { month: 'long' }).toUpperCase();
      const firstYear = firstDate.getFullYear();
      const firstFullDate = firstDate.toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }).toUpperCase();
    
      // Update header month and date
      const monthSummaryDiv = document.getElementById('reportmonthsummary');
      monthSummaryDiv.innerHTML = `
        <p style="text-align: center; line-height: 1;">
          <span style="font-family:Cambria;font-size:12px;">
            <strong>For the Month of ${firstMonth} CY ${firstYear}</strong>
          </span>
        </p>
      `;
    
        // Update Table 2 values
      const summaryTable = document.querySelector(".report-summary");
    
      if (summaryTable) {
        const table2Rows = summaryTable.querySelectorAll("table:nth-of-type(2) tr");
    
        // Update each row with respective values
        if (table2Rows.length >= 5) {
          // Row indices: [1]=barangay_official, [2]=sk_official, [3]=barangay_tanod, [4]=total
          table2Rows[1].children[1].innerHTML = `<p style="text-align:left; margin: 0;"><strong><span style="font-family:Arial;font-weight:bold;font-size:13px;">${barcoSummaryData[0].barangay_official}</span></strong></p>`;
          table2Rows[2].children[1].innerHTML = `<p style="text-align:left; margin: 0;"><strong><span style="font-family:Arial;font-weight:bold;font-size:13px;">${barcoSummaryData[0].sk_official}</span></strong></p>`;
          table2Rows[3].children[1].innerHTML = `<p style="text-align:left; margin: 0;"><strong><span style="font-family:Arial;font-weight:bold;font-size:13px;">${barcoSummaryData[0].barangay_tanod}</span></strong></p>`;
    
    
          // Calculate and update total
          const total = barcoSummaryData[0].barangay_official + barcoSummaryData[0].sk_official + barcoSummaryData[0].barangay_tanod;
          table2Rows[4].children[1].innerHTML = `<p style="text-align:left; margin: 0;"><strong><span style="font-family:Arial;font-weight:bold;font-size:13px;">${total}</span></strong></p>`;
    
    
          // Update SCORE formula text
          const scoreFormula = summaryTable.querySelector("table:nth-of-type(2) tr:nth-child(2) td[rowspan]");
          if (scoreFormula) {
            const fullTotal = 31; // From Table 1
            const score = ((total / fullTotal) * 20).toFixed(2);
            scoreFormula.innerHTML = `
              <p style="margin-top: 8pt; margin-bottom: 4pt; text-align:left;"><strong><span style="font-family:Arial; font-size:11px;">[Total of Table 2/ Total</span></strong></p>
              <p style="margin-top: 8pt; margin-bottom: 4pt; text-align:left;"><strong><span style="font-family:Arial; font-size:11px;">of Table 1] x 20 =</span></strong></p>
              <p style="margin-top: 0; text-align:left;"><strong><span style="font-family:Arial; font-size:11px;">${total}/${fullTotal} x 20 = </span></strong>
              <strong><span style="font-family:Arial; font-size:16px;">${score}</span></strong></p>
            `;
          }
        }
    
        // Update image src
        const barcoImg = summaryTable.querySelector("img[src='imagepath']");
        if (barcoImg) {
          barcoImg.src = `${barcoSummaryData[0].barco_photo_path}`;
        }
      }
    }
}

function updateStatus(){
    const year_month = document.querySelector(".yearmonth").textContent;
    const status = 'Sent.';
    // Create a FormData object to send the file
    const formData = new FormData();
    formData.append('year_month', year_month);
    formData.append('status', status);

    const detailsUrl = `http://localhost:8001/api/service/safetymonitoring/clrngopsUpdate`;
        fetch(detailsUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if the response is okay (status 200-299)
        if (response.ok) {
            Swal.fire({
                title: "Marked as sent.",
                text: "The report is marked as sent.",
                icon: "success",
                confirmButtonColor: "#0f0"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelector('.close-button').click();
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
                      document.querySelector('.close-button').click();
                    }
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
              document.querySelector('.close-button').click();
            }
        });
    });
}

let clearingIndex = 1;

  function addStreet(button) {
    const container = button.parentElement.querySelector('.streetsContainer');
    const parentIndex = [...document.querySelectorAll('.clearing-op')].indexOf(button.closest('.clearing-op'));
    const streetIndex = container.children.length;

    const div = document.createElement('div');
    div.classList.add('street-entry');
    div.innerHTML = `
      <div class="mb-3">
        <label class="form-label">Street Name:</label>
        <input type="text" class="form-control" name="street_name_${parentIndex}[]">
      </div>
      <div class="mb-3">
        <label class="form-label">Road Length:</label>
        <input type="text" class="form-control" name="road_length_${parentIndex}[]">
      </div>
      <div class="mb-3">
        <label class="form-label">Photos (up to 3):</label>
        <input type="file" class="form-control mb-2" name="clearing_photos_${parentIndex}_${streetIndex}[]" accept="image/*">
        <input type="file" class="form-control mb-2" name="clearing_photos_${parentIndex}_${streetIndex}[]" accept="image/*">
        <input type="file" class="form-control" name="clearing_photos_${parentIndex}_${streetIndex}[]" accept="image/*">
      </div>
      <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeStreet(this)">❌ Remove Street</button>
    `;
    container.appendChild(div);
  }

  function removeStreet(button) {
    button.closest('.street-entry').remove();
  }

  function addClearingOp() {
    const container = document.getElementById("clearingOpsContainer");
    const index = clearingIndex;

    const card = document.createElement('div');
    card.classList.add('card', 'clearing-op');
    card.innerHTML = `
      <div class="card-body">
        <h5 class="card-title">Clearing Operation</h5>
        <div class="mb-3">
        <label class="form-label">Date of Operation:</label>
        <input type="date" class="form-control" name="clearing_date[]">
        </div>
        <div class="mb-3">
          <label class="form-label">Conducted:</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="clearing_conducted_${index}" value="Yes">
            <label class="form-check-label">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="clearing_conducted_${index}" value="No">
            <label class="form-check-label">No</label>
          </div>
        </div>

        <div class="streetsContainer">
          <div class="street-entry">
            <div class="mb-3">
              <label class="form-label">Street Name:</label>
              <input type="text" class="form-control" name="street_name_${index}[]">
            </div>
            <div class="mb-3">
              <label class="form-label">Road Length:</label>
              <input type="text" class="form-control" name="road_length_${index}[]">
            </div>
            <div class="mb-3">
              <label class="form-label">Photos (Before and after):</label>
              <input type="file" class="form-control mb-2" name="clearing_photos_${index}_0[]" accept="image/*">
              <input type="file" class="form-control mb-2" name="clearing_photos_${index}_0[]" accept="image/*">
              <input type="file" class="form-control" name="clearing_photos_${index}_0[]" accept="image/*">
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeStreet(this)">❌ Remove Street</button>
          </div>
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addStreet(this)">➕ Add Street</button>

        <div class="mb-3">
          <label class="form-label">Action Taken:</label>
          <textarea class="form-control" name="clearing_action_taken[]"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Remarks:</label>
          <textarea class="form-control" name="clearing_remarks[]"></textarea>
        </div>
        <button type="button" class="btn btn-outline-danger" onclick="removeClearingOp(this)">❌ Remove Operation</button>
      </div>
    `;
    container.appendChild(card);
    clearingIndex++;
  }

  function removeClearingOp(button) {
    button.closest(".clearing-op").remove();
    clearingIndex--;
  }

  document.getElementById("activityForm").addEventListener("submit", function(event) {
  event.preventDefault(); // prevents page reload
  
  const form = this;
  let isValid = true;

  // Remove old error messages and invalid styles
  form.querySelectorAll('.error-message').forEach(e => e.remove());
  form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));

  // Validate each clearing operation
  form.querySelectorAll('.clearing-op').forEach((op, opIndex) => {
    // ✅ Validate Date of Operation
    const dateInput = op.querySelector('input[name="clearing_date[]"]');
    const dateValue = dateInput.value;
    const today = new Date().toISOString().split("T")[0];

    if (!dateValue) {
      showError(dateInput, 'Date of operation is required.');
      isValid = false;
    } /* else if (dateValue > today) {
      showError(dateInput, 'Date cannot be in the future.');
      isValid = false;
    } */

    // ✅ Validate Conducted (radio)
    const conductedRadios = op.querySelectorAll(`input[name="clearing_conducted_${opIndex}"]`);
    const conductedChecked = [...conductedRadios].some(r => r.checked);
    if (!conductedChecked) {
      showError(conductedRadios[0].closest('.mb-3'), 'Please select if conducted.');
      isValid = false;
    }

    // ✅ Validate Streets
    const streets = op.querySelectorAll('.street-entry');
    if (streets.length === 0) {
      showError(op.querySelector('.streetsContainer'), 'At least one street is required.');
      isValid = false;
    }

    streets.forEach((street, streetIndex) => {
      const streetName = street.querySelector('input[name^="street_name"]');
      const roadLength = street.querySelector('input[name^="road_length"]');
      const photoInputs = street.querySelectorAll('input[type="file"]');

      if (!streetName.value.trim()) {
        showError(streetName, 'Street name is required.');
        isValid = false;
      }

      if (!roadLength.value.trim()) {
        showError(roadLength, 'Road length is required.');
        isValid = false;
      }

      photoInputs.forEach((input, idx) => {
        if (!input.files || input.files.length === 0) {
          showError(input, `Photo ${idx + 1} is required.`);
          isValid = false;
        } else {
          const file = input.files[0];
          if (!file.type.startsWith('image/')) {
            showError(input, `Photo ${idx + 1} must be an image.`);
            isValid = false;
          }
        }
      });
    });

    // ✅ Validate Action Taken
    const action = op.querySelector('textarea[name="clearing_action_taken[]"]');
    if (!action.value.trim()) {
      showError(action, 'Action Taken is required.');
      isValid = false;
    }

    // ✅ Validate Remarks
    const remarks = op.querySelector('textarea[name="clearing_remarks[]"]');
    if (!remarks.value.trim()) {
      showError(remarks, 'Remarks are required.');
      isValid = false;
    }
  });

  // ✅ Validate BaRCO participants
  ['barangay_official', 'sk_official', 'barangay_tanod'].forEach(name => {
    const input = form.querySelector(`[name="${name}"]`);
    if (!input.value.trim() || parseInt(input.value) < 0) {
      showError(input, `Please enter a valid number for ${name.replace('_', ' ')}.`);
      isValid = false;
    }
  });

  // ✅ Validate BaRCO photo
  const barcoPhoto = form.querySelector('input[name="barco_photo"]');
  if (!barcoPhoto.files || barcoPhoto.files.length === 0) {
    showError(barcoPhoto, 'BaRCO photo is required.');
    isValid = false;
  } else if (!barcoPhoto.files[0].type.startsWith('image/')) {
    showError(barcoPhoto, 'Only image files are allowed.');
    isValid = false;
  }

  event.preventDefault();
  if (!isValid) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  // Submit using fetch
  const formData = new FormData(form);

  fetch('http://localhost:8001/api/service/safetymonitoring/save_activity', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        title: "Create User!",
        text: "The user is created.",
        icon: "success",
        confirmButtonColor: "#0f0"
    }).then((result) => {
        if (result.isConfirmed) {
            form.reset();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.getElementById('closereportBtn').click();
            fetchTable();
        }
    });
    } else {
      Swal.fire({
        title: "Create cancelled!",
        text: 'There was a problem: ' + (data.message || 'Unknown error'),
        icon: "success",
        confirmButtonColor: "#0f0"
    }).then((result) => {
        if (result.isConfirmed) {
            form.reset();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.getElementById('closereportBtn').click();
            fetchTable();
        }
    });
    }
  })
  .catch(error => {
      // Check for specific error message
      Swal.fire({
          title: "Create Cancelled!",
          text: `Error: ${error}`,
          icon: "error",
          confirmButtonColor: "#0f0"
      }).then((result) => {
          if (result.isConfirmed) {
            form.reset();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.getElementById('closereportBtn').click();
            fetchTable();
          }
      });
  });
});


function showError(input, message) {
  const error = document.createElement('div');
  error.className = 'error-message text-danger small mt-1';
  error.textContent = message;

  input.classList.add('is-invalid');

  // Insert after the field (not inside)
  if (input.nextSibling) {
    input.parentNode.insertBefore(error, input.nextSibling);
  } else {
    input.parentNode.appendChild(error);
  }
}


function openModal() {
  document.getElementById("reportmodal").style.display = "block";
  document.body.classList.add('modal-open'); // Disable body scrolling
}

function closeModal() {
  document.getElementById("reportmodal").style.display = "none";
  document.body.classList.remove('modal-open'); // Re-enable body scrolling
  window.location.reload();

}

// Optional: close modal by clicking outside
window.onclick = function(event) {
  const modal = document.getElementById("reportmodal");
  if (event.target === modal) {
    modal.style.display = "none";
  }
}



fetchTable(currentcategory, searchinput);