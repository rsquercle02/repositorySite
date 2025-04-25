//Fetching census info//
// Global variable to store the anonymity status
let anonymityStatus = 'non-anonymous';

// Variable to store fetched data
let data = [];

// Fetch data from an API or server endpoint
function fetchData() {
  document.getElementById('loadingMessage').style.display = 'block';
  fetch('https://bfmsi.smartbarangayconnect.com/api/service/integration/census')
    .then(response => response.json())
    .then(responseData => {
      console.log('Fetched data:', responseData);

      /* if (Array.isArray(responseData.data)) {
        data = responseData.data.map(item => ({
          _id: item.id,
          firstname: item.firstname || 'FirstName',
          middlename: item.middlename || 'MiddleName',
          lastname: item.lastname || 'LastName',
          householdMembers: item.householdMembers || []
        }));
      } else {
        console.error('Error: The fetched data is invalid.');
      } */

      console.log('Processed data:', data);
      // Hide loading and show form if still non-anonymous
      if (anonymityStatus === 'non-anonymous') {
        document.getElementById('loadingMessage').style.display = 'none';
        document.getElementById('formSection').style.display = 'block';
        document.getElementById('result').style.display = 'block';
        enableFormFields();
      }
    })
    .catch(error => {
      console.error('Error fetching data:', error);
      document.getElementById('loadingMessage').innerText = 'Error loading data.';
    });
}

// Function to enable the form fields
function enableFormFields() {
  document.getElementById('firstname').disabled = false;
  document.getElementById('middlename').disabled = false;
  document.getElementById('lastname').disabled = false;
  document.querySelector('button[id="searchbtn"]').disabled = false;
}

// Search function (case insensitive)
function searchResident(firstname, middlename, lastname) {
  const query = (firstname + " " + middlename + " " + lastname).toLowerCase();

  for (const person of data) {
    const personFullName = (person.firstname + " " + person.middlename + " " + person.lastname).toLowerCase();
    if (personFullName === query) {
      return 'resident';
    }
    for (const householdMember of person.householdMembers) {
      const householdMemberFullName = (householdMember.firstname + " " + householdMember.middlename + " " + householdMember.lastname).toLowerCase();
      if (householdMemberFullName === query) {
        return 'resident';
      }
    }
  }
  return 'no match found';
}

// Event listener for anonymous option change
document.getElementById('anonymousOption').addEventListener('change', function() {
  anonymityStatus = this.value;
  console.log('Anonymity status changed to:', anonymityStatus);

  if (anonymityStatus === 'non-anonymous') {
    if (data.length > 0) {
      // Data already loaded
      document.getElementById('loadingMessage').style.display = 'none';
      document.getElementById('formSection').style.display = 'block';
      document.getElementById('result').style.display = 'block';
      enableFormFields();
    } else {
      // Need to fetch data
      document.getElementById('loadingMessage').style.display = 'block';
      document.getElementById('formSection').style.display = 'none';
      document.getElementById('result').style.display = 'none';
      fetchData();
    }
  } else {
    // Hide everything for anonymous
    document.getElementById('loadingMessage').style.display = 'none';
    document.getElementById('formSection').style.display = 'none';
    document.getElementById('result').style.display = 'none';
  }
});

// Event listener for the search form
document.getElementById('searchbtn').addEventListener('click', function(event) {
  event.preventDefault();

  document.getElementById('firstnameErr').innerHTML = '';
  document.getElementById('middlenameErr').innerHTML = '';
  document.getElementById('lastnameErr').innerHTML = '';

  const firstname = document.getElementById('firstname').value;
  const middlename = document.getElementById('middlename').value;
  const lastname = document.getElementById('lastname').value;

  let isValid = true;

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

    if(isValid){
        const result = searchResident(firstname, middlename, lastname);

        document.getElementById('result').innerText = result === 'resident'
            ? 'This person is a resident.'
            : 'No match found.';
    }
});

// Set default values and fetch data when page loads
window.onload = function() {
  document.getElementById('anonymousOption').value = 'non-anonymous';
  anonymityStatus = 'non-anonymous';
  console.log('Initial anonymity status:', anonymityStatus);

  fetchData();
}

// Fetching business information //
// Global variable to track record status
let recordStatus = '';

// Fetch store data from API, passing query as a parameter
const detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/integration/business`;

fetch(detailsUrl)
  .then(response => response.json())
  .then(data => {
    const searchBox = document.getElementById('searchBox');
    const suggestionsBox = document.getElementById('suggestions');
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
            businessNameField.value = item.business_name;
            businessAddressField.value = item.business_address;

            // Make fields non-editable again
            businessNameField.readOnly = true;
            businessAddressField.readOnly = true;
            suggestionsBox.style.display = 'none';

            // Set record status to 'has records'
            recordStatus = 'has records';
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
          businessNameField.value = '';
          businessAddressField.value = '';
          businessNameField.readOnly = false;
          businessAddressField.readOnly = false;
          suggestionsBox.style.display = 'none';
          document.getElementById("searchBox").value = "No records, add store name and address."
          
          

          // Set record status to 'no records'
          recordStatus = 'no records';
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

    document.getElementById('storeNameErr').innerHTML = '';
    document.getElementById('storeAddressErr').innerHTML = '';
    document.getElementById('concernsErr').innerHTML = '';
    document.getElementById('image1Err').innerHTML = '';
    document.getElementById('image2Err').innerHTML = '';
    document.getElementById('image3Err').innerHTML = '';
    document.getElementById('firstnameErr').innerHTML = '';
    document.getElementById('middlenameErr').innerHTML = '';
    document.getElementById('lastnameErr').innerHTML = '';

    // Collect form data
    let firstname = null;
    let middlename = null;
    let lastname = null;
    const result = document.getElementById('result').innerText;
    const storeName = document.getElementById('storeName').value;
    const storeAddress = document.getElementById('storeAddress').value;
    const concerns = document.getElementById('concerns').value;
    const image1 = document.getElementById('image1').files[0];
    const image2 = document.getElementById('image2').files[0];
    const image3 = document.getElementById('image3').files[0];

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

        if((result == "No match found.") || (result == "Enter details.")){
            isValid = false;
        }
    }

    if(anonymityStatus == "anonymous"){
        firstname = "none";
        middlename = "none";
        lastname = "none";
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

    if(!image2){
        document.getElementById('image2Err').innerHTML = 'Upload image2.';
        isValid = false;
    }

    if(!image3){
        document.getElementById('image3Err').innerHTML = 'Upload image3.';
        isValid = false;
    }

    if(isValid){
        //alert(`The data are : ${storeName}, ${storeAddress}, ${violations}.`);

        // Create a FormData object to send the file
        const formData = new FormData();
        formData.append('firstname', firstname);
        formData.append('middlename', middlename);
        formData.append('lastname', lastname);
        formData.append('anonymityStatus', anonymityStatus);
        formData.append('recordStatus', recordStatus);
        formData.append('storeName', storeName);
        formData.append('storeAddress', storeAddress);
        formData.append('concerns', concerns);
        formData.append('image1', image1);
        formData.append('image2', image2);
        formData.append('image3', image3);

        const detailsUrl = `https://bfmsi.smartbarangayconnect.com/api/service/concerns/postconcerns`;
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