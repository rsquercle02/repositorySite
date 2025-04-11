function fetchTable(){
    const tableBody = document.querySelector("#table tbody");
    // Clear the table before adding new rows
    //tableBody.innerHTML = ''; // This will remove all the previous rows
    // Example URL for fetching detailed information (you may adjust it)
    const detailsUrl = 'http://localhost:8001/api/service/usermanagement/fetch';
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
                idCell.textContent = item.account_id;
                row.appendChild(idCell);

                const fullnameCell = document.createElement("td");
                fullnameCell.textContent = item.full_name;
                row.appendChild(fullnameCell);

                const emailCell = document.createElement("td");
                emailCell.textContent = item.email;
                row.appendChild(emailCell);

                const usertypeCell = document.createElement("td");
                usertypeCell.textContent = item.user_type;
                row.appendChild(usertypeCell);

                const barangayroleCell = document.createElement("td");
                barangayroleCell.textContent = item.barangay_role;
                row.appendChild(barangayroleCell);

                const statusCell = document.createElement("td");
                statusCell.textContent = item.status;
                row.appendChild(statusCell);

                const pictureCell = document.createElement("td");
                pictureCell.textContent = item.picture;
                row.appendChild(pictureCell);

                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "Edit";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#previewModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.account_id);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    //search function
    function search(searchTerm){
        const tableBody = document.querySelector("#table tbody");
        // Clear the table before adding new rows
        tableBody.innerHTML = ''; //This will remove all the previous rows
        // Fetch data from the API endpoint to populate the table
    fetch(`http://localhost:8001/api/service/usermanagement/search/${searchTerm}`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.querySelector("#table tbody");
            tableBody.innerHTML = ''; //This will remove all the previous rows
            data.forEach(item => {
                const row = document.createElement("tr");

                // Create table data cells dynamically
                const idCell = document.createElement("td");
                idCell.textContent = item.account_id;
                row.appendChild(idCell);

                const fullnameCell = document.createElement("td");
                fullnameCell.textContent = item.full_name;
                row.appendChild(fullnameCell);

                const emailCell = document.createElement("td");
                emailCell.textContent = item.email;
                row.appendChild(emailCell);

                const usertypeCell = document.createElement("td");
                usertypeCell.textContent = item.user_type;
                row.appendChild(usertypeCell);

                const barangayroleCell = document.createElement("td");
                barangayroleCell.textContent = item.barangay_role;
                row.appendChild(barangayroleCell);

                const statusCell = document.createElement("td");
                statusCell.textContent = item.status;
                row.appendChild(statusCell);

                const pictureCell = document.createElement("td");
                pictureCell.textContent = item.picture;
                row.appendChild(pictureCell);


                // Create the button and add it to the last column
                const actionCell = document.createElement("td");
                const button = document.createElement("button");
                button.textContent = "Edit";
                button.classList.add("btn", "btn-success");
                button.setAttribute("data-bs-toggle", "modal");
                button.setAttribute("data-bs-target", "#previewModal");

                // Add event listener to the button
                button.addEventListener('click', function() {
                    fetchItemDetails(item.account_id);
                });

                actionCell.appendChild(button);
                row.appendChild(actionCell);

                // Append the row to the table body
                tableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    function fetchItemDetails(id) {
        // Example URL for fetching detailed information (you may adjust it)
        const detailsUrl = `http://localhost:8001/api/service/usermanagement/fetchId/${id}`;

        fetch(detailsUrl)
            .then(response => response.json())
            .then(data => {
                document.getElementById("previewId").innerText = data.account_id;
                document.getElementById("previewFullname").innerText = data.full_name;
                document.getElementById("previewUsername").innerText = data.email;
                //document.getElementById("editPassword").value = data.password;
                document.getElementById("previewProfile").innerText = data.user_type;
                document.getElementById("previewStatus").innerText = data.status;
                // Remove the "../../" from the beginning of the path
                data.picture = data.picture.replace(/^(\.\.\/){2}/, '');
                document.getElementById("previewPicture").src = data.picture;
                //document.getElementById("barangayClearance").innerHTML = '';
                //document.getElementById("businessPermit").innerHTML = '';
                //document.getElementById("occupancyCertificate").innerHTML = '';
                //document.getElementById("taxCertificate").innerHTML = '';
                
            })
            .catch(error => console.error('Error fetching details:', error));
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

    //***************To show fallback image if the image error***********/
    // Fallback image URL
    const fallbackImage = 'assets/images/anonymous.svg';  // Replace this with the path to your fallback image

    // Function that runs if the image cannot be loaded
    function imageError() {
        const img = document.getElementById('previewPicture');
        img.src = fallbackImage;  // Set the fallback image
        img.alt = 'Fallback Image';  // Update alt text for accessibility
    }

    // Function to preview the selected image
    function previewNewPicture(event) {
        const file = event.target.files[0]; // Get the file
        const reader = new FileReader();

        // When the file is read, set the image preview source
        reader.onload = function() {
        const previewNewPicture = document.getElementById('previewNewPicture');
        previewNewPicture.src = reader.result;
        //editPicture.style.display = 'block'; // Show the preview image
        };

        if (file) {
        reader.readAsDataURL(file); // Read the file as a data URL
        }
    }

    document.getElementById('picture').addEventListener('change', function(event){
        previewNewPicture(event);
    });

    //*************For Adding User*********/
    document.getElementById('createUser').addEventListener('click', function(){
        document.getElementById('firstnameErr').innerHTML = '';
        document.getElementById('middlenameErr').innerHTML = '';
        document.getElementById('lastnameErr').innerHTML = '';
        document.getElementById('genderErr').innerHTML = '';
        document.getElementById('emailErr').innerHTML = '';
        document.getElementById('passwordErr').innerHTML = '';
        document.getElementById('usertypeErr').innerHTML = '';
        document.getElementById('broleErr').innerHTML = '';
        document.getElementById('pictureErr').innerHTML = '';

        const firstname = document.getElementById('firstname').value;
        const middlename = document.getElementById('middlename').value;
        const lastname = document.getElementById('lastname').value;
        const gender = document.querySelector('input[name="gender"]:checked');
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const usertype = document.getElementById('usertype').value;
        const brole = document.getElementById('brole').value;
        const pictureInput = document.getElementById('picture');
        const picture = pictureInput.files[0];

        let isValid = true;
        
        if(firstname === ''){
            document.getElementById('firstnameErr').innerHTML = 'Enter first name.';
            isValid = false;
        }

        if(middlename === ''){
            document.getElementById('middlenameErr').innerHTML = 'Enter middle name.';
            isValid = false;
        }

        if(lastname === ''){
            document.getElementById('lastnameErr').innerHTML = 'Enter last name.';
            isValid = false;
        }

        if(!gender){
            document.getElementById('genderErr').innerHTML = 'Enter gender.';
            isValid = false;
        }

        if(email === ''){
            document.getElementById('emailErr').innerHTML = 'Enter email.';
            isValid = false;
        }

        if(password === ''){
            document.getElementById('passwordErr').innerHTML = 'Enter password.';
            isValid = false;
        }

        if(usertype === ''){
            document.getElementById('usertypeErr').innerHTML = 'Choose user type.';
            isValid = false;
        }

        if(brole === ''){
            document.getElementById('broleErr').innerHTML = 'Choose barangay role.';
            isValid = false;
        }

        if(!picture){
            document.getElementById('pictureErr').innerHTML = 'Choose picture.';
            isValid = false;
        }

        if(isValid){
            // Get the file from the input
            const pictureInp = document.getElementById('choosePiInp').files[0];
            //const file = pictureInp.files[0];

            // Create a FormData object to send the file
            const formData = new FormData();
            formData.append('firstname', firstname);
            formData.append('middlename', middlename);
            formData.append('lastname', lastname);
            formData.append('gender', gender);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('usertype', usertype);
            formData.append('brole', brole);
            formData.append('picture', picture);

                const detailsUrl = `http://localhost:8001/api/service/usermanagement/createUser`;
                fetch(detailsUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Create User!",
                        text: "The user is created.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchTable();
                            document.getElementById('closeFormBtn').click();
                            document.getElementById("createUsrForm").reset();
                            document.getElementById("previewNewPicture").src = 'assets/images/anonymous.svg';
                        }
                    });
                } else {
                // If the response is not ok, parse the error response
                return response.json().then(errorData => {
                        Swal.fire({
                            title: "Create Cancelled!",
                            text: `Error: ${errorData.error}`,
                            icon: "error",
                            confirmButtonColor: "#0f0"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetchTable();
                                document.getElementById('closeFormBtn').click();
                                document.getElementById("createUsrForm").reset();
                                document.getElementById("previewNewPicture").src = 'asstes/images/anonymous.svg';
                            }
                        });
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
                        fetchTable();
                        document.getElementById('closeFormBtn').click();
                        document.getElementById("createUsrForm").reset();
                        document.getElementById("previewNewPicture").src = 'assets/images/anonymous.svg';
                    }
                });
            });
        }
    });

    //*************For Update Fullname*********/
    /*
    let id = null;
    const editFnBtn = document.getElementById("editFnBtn");
    editFnBtn.setAttribute("data-bs-toggle", "modal");
    editFnBtn.setAttribute("data-bs-target", "#editFnModal");

    let previewFullname = null;
    let editFullname = null;

    editFnBtn.addEventListener('click', function() {
        id = document.getElementById('previewId').textContent;
        previewFullname = document.getElementById('previewFullname').textContent;
        document.getElementById('editFnErr').innerHTML = '';
        document.getElementById('editFullname').value = previewFullname;
    });

    document.getElementById('updateFullname').addEventListener('click', function(){
        editFullname = document.getElementById('editFullname').value;
        let isValid = true;

        if(editFullname === ''){
            document.getElementById('editFnErr').innerHTML = 'No value.';
            isValid = false;
        }
        
        if(previewFullname === editFullname){
            document.getElementById('editFnErr').innerHTML = 'No changes.';
            isValid = false;
        }

        if(isValid){
            const formData = {editFullname: editFullname};

                const detailsUrl = `http://localhost:8001/api/service/usermanagement/updateFullname/${id}`;
                fetch(detailsUrl, {
                    method: 'PUT',
                    headers: {
                    'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                },
                    body: JSON.stringify(formData),
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Update Information!",
                        text: "The information is updated.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchTable();
                            document.getElementById('closePreviewBtn').click();
                            document.getElementById('closeFnModal').click();
                        }
                    });
                } else {
                // If the response is not ok, parse the error response
                return response.json().then(errorData => {
                        Swal.fire({
                            title: "Scheduling Cancelled!",
                            text: `Error: ${errorData.error}`,
                            icon: "error",
                            confirmButtonColor: "#0f0"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('closeFnModal').click();
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
                        fetchTable();
                        document.getElementById('closePreviewBtn').click();
                        document.getElementById('closeFnModal').click();
                    }
                });
            });
        }
    });
    */

    //*************For Update Username*********/
    const editUnBtn = document.getElementById("editUnBtn");
    editUnBtn.setAttribute("data-bs-toggle", "modal");
    editUnBtn.setAttribute("data-bs-target", "#editUnModal");

    let previewUsername = null;
    let editUsername = null;

    editUnBtn.addEventListener('click', function() {
        id = document.getElementById('previewId').textContent;
        previewUsername = document.getElementById('previewUsername').textContent;
        document.getElementById('editUnErr').innerHTML = '';
        document.getElementById('editUsername').value = previewUsername;
    });

    document.getElementById('updateUsername').addEventListener('click', function(){
        editUsername = document.getElementById('editUsername').value;
        let isValid = true;

        if(editUsername === ''){
            document.getElementById('editUnErr').innerHTML = 'No value.';
            isValid = false;
        }
        
        if(previewUsername === editUsername){
            document.getElementById('editUnErr').innerHTML = 'No changes.';
            isValid = false;
        }

        // Regular expression for validating email format
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // Test if the email matches the pattern
        if (!emailPattern.test(editUsername)) {
            document.getElementById('editUnErr').innerHTML = 'Not email.';
            isValid = false;
        }

        if(isValid){
            const formData = {editUsername: editUsername};

                const detailsUrl = `http://localhost:8001/api/service/usermanagement/updateEmail/${id}`;
                fetch(detailsUrl, {
                    method: 'PUT',
                    headers: {
                    'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                },
                    body: JSON.stringify(formData),
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Update Information!",
                        text: "The information is updated.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchTable();
                            document.getElementById('closePreviewBtn').click();
                            document.getElementById('closeUnModal').click();
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
                                fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('closeUnModal').click();
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
                        fetchTable();
                        document.getElementById('closePreviewBtn').click();
                        document.getElementById('closeUnModal').click();
                    }
                });
            });
        }
    });

    //*************For Update Profile*********/
    const editPrBtn = document.getElementById("editPrBtn");
    editPrBtn.setAttribute("data-bs-toggle", "modal");
    editPrBtn.setAttribute("data-bs-target", "#editPrModal");

    let previewProfile = null;
    let editProfile = null;

    editPrBtn.addEventListener('click', function() {
        id = document.getElementById('previewId').textContent;
        previewProfile = document.getElementById('previewProfile').textContent;
        document.getElementById('editPrErr').innerHTML = '';
        document.getElementById('editProfile').value = previewProfile;
    });

    document.getElementById('updateProfile').addEventListener('click', function(){
        editProfile = document.getElementById('editProfile').value;
        let isValid = true;

        if(editProfile === 'Choose Profile'){
            document.getElementById('editPrErr').innerHTML = 'No profile.';
            isValid = false;
        }
        
        if(previewProfile === editProfile){
            document.getElementById('editPrErr').innerHTML = 'No changes.';
            isValid = false;
        }

        if(isValid){
            const formData = {editProfile: editProfile};

                const detailsUrl = `http://localhost:8001/api/service/usermanagement/updateUsertype/${id}`;
                fetch(detailsUrl, {
                    method: 'PUT',
                    headers: {
                    'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                },
                    body: JSON.stringify(formData),
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Update Information!",
                        text: "The information is updated.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchTable();
                            document.getElementById('closePreviewBtn').click();
                            document.getElementById('closePrModal').click();
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
                                fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('closePrModal').click();
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
                        fetchTable();
                        document.getElementById('closePreviewBtn').click();
                        document.getElementById('closePrModal').click();
                    }
                });
            });
        }
    });

    //*************For Update Status*********/
    const editStBtn = document.getElementById("editStBtn");
    editStBtn.setAttribute("data-bs-toggle", "modal");
    editStBtn.setAttribute("data-bs-target", "#editStModal");

    let previewStatus = null;
    let editstatus = null;

    editStBtn.addEventListener('click', function() {
        id = document.getElementById('previewId').textContent;
        previewStatus = document.getElementById('previewStatus').textContent;
        document.getElementById('editStErr').innerHTML = '';
        document.getElementById('editStatus').value = previewStatus;
    });

    document.getElementById('updateStatus').addEventListener('click', function(){
        editStatus = document.getElementById('editStatus').value;
        let isValid = true;

        if(editStatus === 'Choose Status'){
            document.getElementById('editStErr').innerHTML = 'No status.';
            isValid = false;
        }
        
        if(previewStatus === editStatus){
            document.getElementById('editStErr').innerHTML = 'No changes.';
            isValid = false;
        }

        if(isValid){
            const formData = {editStatus: editStatus};

                const detailsUrl = `http://localhost:8001/api/service/usermanagement/updateStatus/${id}`;
                fetch(detailsUrl, {
                    method: 'PUT',
                    headers: {
                    'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                },
                    body: JSON.stringify(formData),
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Update Information!",
                        text: "The information is updated.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchTable();
                            document.getElementById('closePreviewBtn').click();
                            document.getElementById('closeStModal').click();
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
                                fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('closeStModal').click();
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
                        fetchTable();
                        document.getElementById('closePreviewBtn').click();
                        document.getElementById('closeStModal').click();
                    }
                });
            });
        }
    });

    //*************For Update Password*********/
    const editPsBtn = document.getElementById("editPsBtn");
    editPsBtn.setAttribute("data-bs-toggle", "modal");
    editPsBtn.setAttribute("data-bs-target", "#editPsModal");

    editPsBtn.addEventListener('click', function() {
        id = document.getElementById('previewId').textContent;
        document.getElementById('editPsErr').innerHTML = '';
        document.getElementById('editPassword').value = '';
    });

    document.getElementById('updatePassword').addEventListener('click', function(){
        editPassword = document.getElementById('editPassword').value;
        document.getElementById('editPsErr').innerHTML = '';
        let isValid = true;

        if(editPassword === ''){
            document.getElementById('editPsErr').innerHTML = 'No Value.';
            isValid = false;
        }

        if(isValid){
            Swal.fire({
                title: "Update password?",
                text: "",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, update."
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = {editPassword: editPassword};

                        const detailsUrl = `http://localhost:8001/api/service/usermanagement/updatePassword/${id}`;
                        fetch(detailsUrl, {
                            method: 'PUT',
                            headers: {
                            'Content-Type': 'application/json',  // Specifies that the body is in JSON format
                        },
                            body: JSON.stringify(formData),
                        })
                        .then(response => {
                            // Check if the response is okay (status 200-299)
                        if (response.ok) {
                            Swal.fire({
                                title: "Update Information!",
                                text: "The information is updated.",
                                icon: "success",
                                confirmButtonColor: "#0f0"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetchTable();
                                    document.getElementById('closePreviewBtn').click();
                                    document.getElementById('closePsModal').click();
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
                                        fetchTable();
                                        document.getElementById('closePreviewBtn').click();
                                        document.getElementById('closePsModal').click();
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
                                fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('closePsModal').click();
                            }
                        });
                    });
                }
            });
        }
    });

    //*************For Update Picture*********/
    /*
    const editPiBtn = document.getElementById("editPiBtn");
    editPiBtn.setAttribute("data-bs-toggle", "modal");
    editPiBtn.setAttribute("data-bs-target", "#editPiModal");

    let previewPicture = null;
    let editPicture = null;

    editPiBtn.addEventListener('click', function() {
        id = document.getElementById('previewId').textContent;
        previewPicture = document.getElementById('previewPicture').src;
        document.getElementById('editPiErr').innerHTML = '';
        document.getElementById('editPicture').src = previewPicture;
    });

    document.getElementById('choosePiBtn').addEventListener('click', function() {
        document.getElementById('choosePiInp').click();
    });

    // Function to preview the selected image
    function previewImage(event) {
        const file = event.target.files[0]; // Get the file
        const reader = new FileReader();
  
        // When the file is read, set the image preview source
        reader.onload = function() {
          const editPicture = document.getElementById('editPicture');
          editPicture.src = reader.result;
          //editPicture.style.display = 'block'; // Show the preview image
        };
  
        if (file) {
          reader.readAsDataURL(file); // Read the file as a data URL
        }
    }

    document.getElementById('choosePiInp').addEventListener('change', function(event) {
        previewImage(event);
    });

    document.getElementById('updatePicture').addEventListener('click', function(event){
        editPicture = document.getElementById('editPicture').src;
        let isValid = true;
        
        if(previewPicture === editPicture){
            document.getElementById('editPiErr').innerHTML = 'No changes.';
            isValid = false;
        }

        if(isValid){
            // Get the file from the input
            const pictureInp = document.getElementById('choosePiInp').files[0];
            //const file = pictureInp.files[0];

            // Create a FormData object to send the file
            const formData = new FormData();
            formData.append('file1', pictureInp); // Append the file to FormData

                const detailsUrl = `http://localhost:8001/api/service/usermanagement/updatePicture/${id}`;
                fetch(detailsUrl, {
                    method: 'PUT',
                    body: formData
                })
                .then(response => {
                    // Check if the response is okay (status 200-299)
                if (response.ok) {
                    Swal.fire({
                        title: "Update Information!",
                        text: "The information is updated.",
                        icon: "success",
                        confirmButtonColor: "#0f0"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchTable();
                            document.getElementById('closePreviewBtn').click();
                            document.getElementById('closePiModal').click();
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
                                fetchTable();
                                document.getElementById('closePreviewBtn').click();
                                document.getElementById('closePiModal').click();
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
                        fetchTable();
                        document.getElementById('closePreviewBtn').click();
                        document.getElementById('closePiModal').click();
                    }
                });
            });
        }
    });
    */
    
    fetchTable();