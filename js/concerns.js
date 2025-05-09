//Fetching census info//
// Global variable to store the anonymity status
let anonymityStatus = 'non-anonymous';

// Census fetch
let jsonData = [];

// Show initial loading message
document.getElementById("result").innerHTML = "<p style='color:blue;'>Loading census data...</p>";

// Load the data.json file using fetch
fetch('https://barangay-api-s4rv.onrender.com/api/cencus')
  .then(response => response.json())
  .then(data => {
    jsonData = data;

    if (jsonData.length === 0) {
      document.getElementById("result").innerHTML = "<p style='color:red;'>No census data available.</p>";
    } else {
      document.getElementById("result").innerHTML = "<p style='color:green;'>Census data loaded. You can now search.</p>";
    }
  })
  .catch(error => {
    console.error('Error fetching JSON data:', error);
    document.getElementById("result").innerHTML = "<p style='color:red;'>Failed to load census data.</p>";
  });

function searchNames() {
  const firstName = document.getElementById("firstname").value.trim().toLowerCase();
  const middleName = document.getElementById("middlename").value.trim().toLowerCase();
  const lastName = document.getElementById("lastname").value.trim().toLowerCase();

  const resultDiv = document.getElementById("result");
  resultDiv.innerHTML = "";

  // Clear errors when fields have input
  if (firstName !== '') {
    document.getElementById('firstnameErr').innerHTML = '';
  }
  if (middleName !== '') {
    document.getElementById('middlenameErr').innerHTML = '';
  }
  if (lastName !== '') {
    document.getElementById('lastnameErr').innerHTML = '';
  }

  // Check if data is loaded
  if (jsonData.length === 0) {
    resultDiv.innerHTML = "<p style='color:blue;'>Census data is still loading or unavailable.</p>";
    return;
  }

  if (firstName && middleName && lastName) {
    const result = jsonData.filter(person =>
      person.firstname.toLowerCase() === firstName &&
      person.middlename.toLowerCase() === middleName &&
      person.lastname.toLowerCase() === lastName
    );

    if (result.length > 0) {
      resultDiv.innerHTML += `<p>Resident.</p>`;
    } else {
      resultDiv.innerHTML = "<p>Not resident.</p>";
    }
  } else {
    resultDiv.innerHTML = "<p>Please fill out all fields to search.</p>";
  }
}


    document.getElementById("searchButton").addEventListener("click", searchNames);
    document.getElementById("firstname").addEventListener("input", searchNames);
    document.getElementById("middlename").addEventListener("input", searchNames);
    document.getElementById("lastname").addEventListener("input", searchNames);

// Event listener for anonymous option change
document.getElementById('anonymousOption').addEventListener('change', function() {
  anonymityStatus = this.value;
  console.log('Anonymity status changed to:', anonymityStatus);

  if (anonymityStatus === 'non-anonymous') {
      document.getElementById('formSection').style.display = 'block';
      document.getElementById('result').style.display = 'block';
  } else {
    // Hide everything for anonymous
    document.getElementById('formSection').style.display = 'none';
    document.getElementById('result').style.display = 'none';
  }
});

// Set default values and fetch data when page loads
window.onload = function() {
  document.getElementById('anonymousOption').value = 'non-anonymous';
  anonymityStatus = 'non-anonymous';
  console.log('Initial anonymity status:', anonymityStatus);
}

// Fetching business information //
// Global variable to track record status
let recordStatus = '';

// Fetch store data from API, passing query as a parameter
const detailsUrl = `http://localhost:8001/api/service/integration/business`;

fetch(detailsUrl)
  .then(response => response.json())
  .then(data => {
    const searchBox = document.getElementById('searchBox');
    const suggestionsBox = document.getElementById('suggestions');
    const businessIdField = document.getElementById('storeId');
    const businessNameField = document.getElementById('storeName');
    const businessAddressField = document.getElementById('storeAddress');
    

    searchBox.addEventListener('input', function() {
      const query = this.value.toLowerCase();
      suggestionsBox.innerHTML = '';
      if (query.length === 0) {
        suggestionsBox.style.display = 'none';
        recordStatus = '';
        return;
      }

      const filtered = data.filter(item =>
        item.business_name.toLowerCase().includes(query)
      );

      if (filtered.length > 0) {
        filtered.forEach(item => {
          const div = document.createElement('div');
          div.classList.add('suggestion-item');
          div.innerHTML = `<strong>${item.business_name}</strong><br><small>${item.business_address}</small>`;
          div.addEventListener('click', () => {
            searchBox.value = item.business_name;
            businessIdField.value = item.id;
            businessNameField.value = item.business_name;
            businessAddressField.value = item.business_address;

            // Make fields non-editable again
            businessIdField.readOnly = true;
            businessNameField.readOnly = true;
            businessAddressField.readOnly = true;
            suggestionsBox.style.display = 'none';

            // Set record status to 'has records'
            recordStatus = 'has records.';
            console.log('Record status:', recordStatus);
          });
          suggestionsBox.appendChild(div);
        });
        suggestionsBox.style.display = 'block';
      } else {
        const noResult = document.createElement('div');
        noResult.classList.add('suggestion-item');
        noResult.textContent = 'No records found.';
        noResult.addEventListener('click', () => {
          // Clear the fields when "No records found" is selected
          businessIdField.value = '';
          businessNameField.value = '';
          businessAddressField.value = '';
          businessNameField.readOnly = false;
          businessAddressField.readOnly = false;
          suggestionsBox.style.display = 'none';
          document.getElementById("searchBox").value = "No records, add store name and address."
          
          

          // Set record status to 'no records'
          recordStatus = 'no records.';
          console.log('Record status:', recordStatus);
        });
        suggestionsBox.appendChild(noResult);
        suggestionsBox.style.display = 'block';
      }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
      if (e.target !== searchBox) {
        suggestionsBox.style.display = 'none';
      }
    });
  })
  .catch(error => console.error('Error fetching data:', error));

  
// Event listener for the submit button
document.getElementById("submitButton").addEventListener("click", function(event) {
    event.preventDefault();  // Prevent form submission

    console.log(anonymityStatus);
    console.log(recordStatus);

    document.getElementById('storeIdErr').innerHTML = '';
    document.getElementById('storeNameErr').innerHTML = '';
    document.getElementById('storeAddressErr').innerHTML = '';
    document.getElementById('concernsErr').innerHTML = '';
    document.getElementById('image1Err').innerHTML = '';
    document.getElementById('firstnameErr').innerHTML = '';
    document.getElementById('middlenameErr').innerHTML = '';
    document.getElementById('lastnameErr').innerHTML = '';

    // Collect form data
    let firstname = null;
    let middlename = null;
    let lastname = null;
    let residentType = null;
    let storeId = null;
    const result = document.getElementById('result').innerText;
    storeId = document.getElementById('storeId').value;
    const storeName = document.getElementById('storeName').value;
    const storeAddress = document.getElementById('storeAddress').value;
    const concerns = document.getElementById('concerns').value;
    const image1 = document.getElementById('image1').files[0];

    let isValid = true;

    if(anonymityStatus == "non-anonymous"){
        firstname = document.getElementById('firstname').value;
        middlename = document.getElementById('middlename').value;
        lastname = document.getElementById('lastname').value;

        if(firstname == ''){
            document.getElementById('firstnameErr').innerHTML = 'Enter firstname.';
            isValid = false;
        }

        if(middlename == ''){
            document.getElementById('middlenameErr').innerHTML = 'Enter middlename.';
            isValid = false;
        }

        if(lastname == ''){
            document.getElementById('lastnameErr').innerHTML = 'Enter lastname.';
            isValid = false;
        }

        if(result == ''){
            document.getElementById('result').innerText = 'Enter details.';
            isValid = false;
        }

        if(result == "Please fill out all fields to search."){
            isValid = false;
        }

        if(result == "Resident."){
          residentType = 'Resident.';
        }

        if(result == "Not resident."){
          residentType = 'Not resident.';
        }

        if((result == "Failed to load census data.") || (result == "Census data is still loading or unavailable.")){
          residentType = 'Unknown.';
        }

    }

    if(anonymityStatus == "anonymous"){
        firstname = "none";
        middlename = "none";
        lastname = "none";
        residentType = 'Unknown.';
    }

    if(recordStatus == 'has records.'){
      storeId = document.getElementById('storeId').value;
      //console.log('has records s1.');
    }

    if(recordStatus == 'no records.'){
      storeId = 'null';
      //console.log('has no records s2.');
    }

    if(storeName == ''){
        document.getElementById('storeNameErr').innerHTML = 'Enter store name.';
        isValid = false;
    }

    if(storeAddress == ''){
        document.getElementById('storeAddressErr').innerHTML = 'Enter store address.';
        isValid = false;
    }

    if(concerns == ''){
        document.getElementById('concernsErr').innerHTML = 'Enter concerns.';
        isValid = false;
    }

    if(!image1){
        document.getElementById('image1Err').innerHTML = 'Upload image1.';
        isValid = false;
    }

    if(isValid){
        //alert(`The data are : ${storeName}, ${storeAddress}, ${violations}.`);

        // Create a FormData object to send the file
        const formData = new FormData();
        formData.append('firstname', firstname);
        formData.append('middlename', middlename);
        formData.append('lastname', lastname);
        formData.append('residentType', residentType);
        formData.append('anonymityStatus', anonymityStatus);
        formData.append('recordStatus', recordStatus);
        formData.append('storeId', storeId);
        formData.append('storeName', storeName);
        formData.append('storeAddress', storeAddress);
        formData.append('concerns', concerns);
        formData.append('image1', image1);

        const detailsUrl = `http://localhost:8001/api/service/concerns/postconcerns`;
            fetch(detailsUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if the response is okay (status 200-299)
            if (response.ok) {
                Swal.fire({
                    title: "Concerns",
                    text: "The concerns is uploaded.",
                    icon: "success",
                    confirmButtonColor: "#0f0"
                }).then((result) => {
                    if (result.isConfirmed) {
                        //fetchTable();
                        //document.getElementById('closeFormBtn').click();
                        document.getElementById("concernsForm").reset();
                        //document.getElementById("previewNewPicture").src = 'assets/images/anonymous.svg';
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
                            //fetchTable();
                            //document.getElementById('closeFormBtn').click();
                            document.getElementById("concernsForm").reset();
                            //document.getElementById("previewNewPicture").src = 'asstes/images/anonymous.svg';
                        }
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
                    //document.getElementById('closeFormBtn').click();
                    document.getElementById("concernsForm").reset();
                }
            });
        });
    }
});