// Event listener for the submit button
document.getElementById("submitButton").addEventListener("click", function(event) {
    event.preventDefault();  // Prevent form submission

    document.getElementById('storeName').innerHTML = '';
    document.getElementById('storeAddress').innerHTML = '';
    document.getElementById('concerns').innerHTML = '';

    // Collect form data
    const storeName = document.getElementById('storeName').value;
    const storeAddress = document.getElementById('storeAddress').value;
    const concerns = document.getElementById('concerns').value;
    const image1 = document.getElementById('image1').files[0];
    const image2 = document.getElementById('image2').files[0];
    const image3 = document.getElementById('image3').files[0];

    let isValid = true;

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
        formData.append('storeName', storeName);
        formData.append('storeAddress', storeAddress);
        formData.append('concerns', concerns);
        formData.append('image1', image1);
        formData.append('image2', image2);
        formData.append('image3', image3);

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
                    //fetchTable();
                    //document.getElementById('closeFormBtn').click();
                    document.getElementById("concernsForm").reset();
                    //document.getElementById("previewNewPicture").src = 'assets/images/anonymous.svg';
                }
            });
        });
    }
});