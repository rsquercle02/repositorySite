//*************For Adding User*********/
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
    const gender = document.querySelector('input[name="gender"]:checked').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
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
    } else {

        // Regular expression for validating email format
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // Test if the email matches the pattern
        if (!emailPattern.test(email)) {
            document.getElementById('emailErr').innerHTML = 'Please enter a valid email address.';
            isValid = false;
        }
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

    /*Password validation */
    if(password === ''){
        document.getElementById('passwordErr').innerHTML = 'No Value.';
        isValid = false;
        return;
    }

    if (password.length < 8) {
        document.getElementById('passwordErr').innerHTML = 'Password must be at least 8 characters long.';
        isValid = false;
        return;
    }

    if (!/[A-Z]/.test(password)) {
        document.getElementById('passwordErr').innerHTML = 'Password must contain at least one uppercase letter.';
        isValid = false;
        return;
    }

    if (!/[a-z]/.test(password)) {
        document.getElementById('passwordErr').innerHTML = 'Password must contain at least one lowercase letter.';
        isValid = false;
        return;
    }

    if (!/\d/.test(password)) {
        document.getElementById('passwordErr').innerHTML = 'Password must contain at least one number.';
        isValid = false;
        return;
    }

    if (!/[!@#$%^&*]/.test(password)) {
        document.getElementById('passwordErr').innerHTML = 'Password must contain at least one special character (!@#$%^&*).';
        isValid = false;
        return;
    }

    if (confirmPassword === '') {
        document.getElementById('passwordErr').innerHTML = 'Re-type password.';
        isValid = false;
        return;
    }

    if (password !== confirmPassword) {
        document.getElementById('passwordErr').innerHTML = 'Passwords do not match.';
        isValid = false;
        return;
    }

    if(isValid){
        // Get the file from the input
        const pictureInp = document.getElementById('picture').files[0];
        //const file = pictureInp.files[0];
        const status = "Active";

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
        formData.append('status', status);
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
                            document.getElementById("createUsrForm").reset();
                            document.getElementById("previewNewPicture").src = 'assets/images/anonymous.svg';
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
                    document.getElementById("createUsrForm").reset();
                    document.getElementById("previewNewPicture").src = 'assets/images/anonymous.svg';
                }
            });
        });
    }
});