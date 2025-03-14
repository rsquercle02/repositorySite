/* Form steps */

        // DOM Elements
        const steps = document.querySelectorAll('.step');
        const progressBar = document.getElementById('progress-bar');
        let currentStep = 0;

        // Show the current step
        function showStep(stepIndex) {
            steps.forEach((step, index) => {
                step.classList.toggle('active', index === stepIndex);
            });

            // Update progress bar
            const progressPercentage = ((stepIndex + 1) / steps.length) * 100;
            progressBar.style.width = progressPercentage + '%';
            progressBar.textContent = `Step ${stepIndex + 1} of ${steps.length}`;
        }

        // Next button for Step 1
        document.getElementById('next-1').addEventListener('click', () => {
            currentStep = 1;
            let isValid = true;

            const errors = document.querySelectorAll('.error');
            errors.forEach(error => error.textContent = '');

            const name = document.getElementById('name').value.trim();
            if (name == "") {
            document.getElementById('nameErr').textContent = 'Enter business name.';
            isValid = false;
            }

            const description = document.getElementById('description').value.trim();
            if (description == "") {
            document.getElementById('descriptionErr').textContent = 'Enter description of the business.';
            isValid = false;
            }

            const email = document.getElementById('email').value.trim();
            if (email == "") {
            document.getElementById('busEmailErr').textContent = 'Enter business email.';
            isValid = false;
            }

            const businessphone = document.getElementById('busPhone').value.trim();
            if (businessphone == "") {
            document.getElementById('busPhoneErr').textContent = 'Enter business phone number.';
            isValid = false;
            }

            const div1 = document.getElementById('div1');
            if (div1.classList.contains('active')){
            const fromDay = document.getElementById('fromDay').value.trim();
            const toDay = document.getElementById('toDay').value.trim();
            if (fromDay == "" || toDay == "") {
            document.getElementById('fxDayErr').textContent = 'Enter operation days.';
            isValid = false;
            }
            }

            const div2 = document.getElementById('div2');
            if (div2.classList.contains('active')){
            const oprtDay = document.getElementById('oprtDay').value.trim();
            if (oprtDay == "") {
            document.getElementById('cusDayErr').textContent = 'Enter operation days.';
            isValid = false;
            }
            }

            const div3 = document.getElementById('div3');
            if (div3.classList.contains('active')){
            const fromTime = document.getElementById('fromTime').value.trim();
            const toTime = document.getElementById('toTime').value.trim();
            if (fromTime == "" || toTime == "") {
            document.getElementById('fxTimeErr').textContent = 'Enter operation time.';
            isValid = false;
            }
            }

            const div4 = document.getElementById('div4');
            if (div4.classList.contains('active')){
            const oprtTime = document.getElementById('oprtTime').value.trim();
            if (oprtTime == "") {
            document.getElementById('cusTimeErr').textContent = 'Enter operation time.';
            isValid = false;
            }
            }

            const regionSelect = document.getElementById('regionSelect').value.trim();
            if (regionSelect == "") {
            document.getElementById('regionErr').textContent = 'Select region.';
            isValid = false;
            }

            const provinceSelect = document.getElementById('provinceSelect').value.trim();
            if (provinceSelect == "") {
            document.getElementById('provinceErr').textContent = 'Select province.';
            isValid = false;
            }

            const municipalitySelect = document.getElementById('municipalitySelect').value.trim();
            if (municipalitySelect == "") {
            document.getElementById('municipalityErr').textContent = 'Select municipality.';
            isValid = false;
            }

            const barangaySelect = document.getElementById('barangaySelect').value.trim();
            if (barangaySelect == "") {
            document.getElementById('barangayErr').textContent = 'Select baragay.';
            isValid = false;
            }

            const sbhNo = document.getElementById('sbhNo').value.trim();
            if (sbhNo == "") {
            document.getElementById('sbhErr').textContent = 'Enter Street name, Building, House no.';
            isValid = false;
            }

            const latitude = document.getElementById('latitude').value.trim();
            const longitude = document.getElementById('longitude').value.trim();
            if ((latitude == "") && (longitude == "")) {
            document.getElementById('longLatErr').textContent = 'Pin address.';
            isValid = false;
            }

            if (!isValid){
                return;
            }

            showStep(currentStep);
        });

        // Next button for Step 2
        document.getElementById('next-2').addEventListener('click', () => {
            currentStep = 2;

            const errors = document.querySelectorAll('.error');
            errors.forEach(error => error.textContent = '');

            const radiobtn = document.getElementsByName('businessType');
            const isValid = Array.from(radiobtn).some(radio => radio.checked);

            if (!isValid){
                document.getElementById('businessTypeErr').textContent = 'Choose business type.';
                return;
            }

            showStep(currentStep);
        });

        // Next button for Step 3
        document.getElementById('next-3').addEventListener('click', () => {
            currentStep = 3;
            let isValid = true;

            const errors = document.querySelectorAll('.error');
            errors.forEach(error => error.textContent = '');

            const firstName = document.getElementById('firstName').value.trim();
            if (firstName == "") {
            document.getElementById('firstNameErr').textContent = 'Enter first name.';
            isValid = false;
            }

            const middleName = document.getElementById('middleName').value.trim();
            if (middleName == "") {
            document.getElementById('middleNameErr').textContent = 'Enter middle name.';
            isValid = false;
            }

            const lastName = document.getElementById('lastName').value.trim();
            if (lastName == "") {
            document.getElementById('lastNameErr').textContent = 'Enter last name.';
            isValid = false;
            }


            const age = document.getElementById('age').value.trim();
            if (age == "") {
            document.getElementById('ageErr').textContent = 'Enter age.';
            isValid = false;
            }

            const role = document.getElementById('role').value.trim();
            if (role == "") {
            document.getElementById('roleErr').textContent = 'Enter role.';
            isValid = false;
            }

            const ownrEmail = document.getElementById('ownrEmail').value.trim();
            if (ownrEmail == "") {
            document.getElementById('ownrEmailErr').textContent = 'Enter email.';
            isValid = false;
            }

            const ownrPhone = document.getElementById('ownrPhone').value.trim();
            if (ownrPhone == "") {
            document.getElementById('ownrPhoneErr').textContent = 'Enter phone number.';
            isValid = false;
            }

            if (!isValid){
                return;
            }
            
            showStep(currentStep);
        });

        // Back button for Step 2
        document.getElementById('prev-2').addEventListener('click', () => {
            currentStep = 0;
            showStep(currentStep);
        });

        // Back button for Step 3
        document.getElementById('prev-3').addEventListener('click', () => {
            currentStep = 1;
            showStep(currentStep);
        });

        // Back button for Step 4
        document.getElementById('prev-4').addEventListener('click', () => {
            currentStep = 2;
            showStep(currentStep);
        });

        // Form submission
        document.getElementById('submit').addEventListener('click', () => {
            let isValid = true;

            const fileInputs = [
                { id: 'barangayClearance', errNoFileId: 'bClNoErr', errFileTypeId: 'bClTypeErr', errFileSizeId: 'bClSizeErr', allowedTypes: ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], maxSize: 5 * 1024 * 1024 }, // 5 MB for image and documents
                { id: 'businessPermit', errNoFileId: 'bPrNoErr', errFileTypeId: 'bPrTypeErr', errFileSizeId: 'bPrSizeErr', allowedTypes: ['image/jpeg', 'image/png','application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], maxSize: 5 * 1024 * 1024 }, // 5 MB for image and documents
                { id: 'occupancyCertificate', errNoFileId: 'oCrNoErr', errFileTypeId: 'oCrTypeErr', errFileSizeId: 'oCrSizeErr', allowedTypes: ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], maxSize: 5 * 1024 * 1024 }, // 5 MB for image and documents
                { id: 'taxCertificate', errNoFileId: 'tCrNoErr', errFileTypeId: 'tCrTypeErr', errFileSizeId: 'tCrSizeErr', allowedTypes: ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], maxSize: 5 * 1024 * 1024 } // 5 MB for image and documents
              ];

            fileInputs.forEach(input => {
            document.getElementById(input.errNoFileId).innerHTML = '';
            document.getElementById(input.errFileTypeId).innerHTML = '';
            document.getElementById(input.errFileSizeId).innerHTML = '';
            });

              // Loop through each file input and validate
            fileInputs.forEach(config => {
                const fileInput = document.getElementById(config.id);
                const files = fileInput.files;

                // No file selected check
                if (files.length === 0) {
                document.getElementById(config.errNoFileId).textContent = `Please select a file for "${fileInput.previousElementSibling.textContent}".`;
                isValid = false;
                } else {
                const file = files[0];

                // File type validation
                if (!config.allowedTypes.includes(file.type)) {
                    document.getElementById(config.errFileTypeId).textContent = `Invalid file type for "${fileInput.previousElementSibling.textContent}". Allowed types are: .jpeg, .png, .pdf, .doc, .docx.`;
                    isValid = false;
                }

                // File size validation
                if (file.size > config.maxSize) {
                    const maxSizeMB = config.maxSize / (1024 * 1024);
                    document.getElementById(config.errFileSizeId).textContent = `The file for "${fileInput.previousElementSibling.textContent}" exceeds the size limit of ${maxSizeMB} MB.`;
                    isValid = false;
                }
                }
            });
            
            if (!isValid){
                return;
            }

            submit();
        });

    /* Operating days and hours */

        // Button-to-Group Mapping
        const buttonGroups = [
            {
                buttons: [document.getElementById('showDiv1'), document.getElementById('showDiv2')],
                divs: [document.getElementById('div1'), document.getElementById('div2')],
            },
            {
                buttons: [document.getElementById('showDiv3'), document.getElementById('showDiv4')],
                divs: [document.getElementById('div3'), document.getElementById('div4')],
            },
        ];

        // Add Event Listeners to Buttons
        buttonGroups.forEach((group) => {
            group.buttons.forEach((button, index) => {
                button.addEventListener('click', () => {
                    // Set active class for buttons
                    group.buttons.forEach((btn) => btn.classList.remove('active'));
                    button.classList.add('active');

                    // Show the corresponding div and hide others
                    group.divs.forEach((div) => div.classList.remove('active'));
                    group.divs[index].classList.add('active');
                });
            });
        });

    /* Business type */

        // Add active class on selection
        document.querySelectorAll('.radio-card input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.radio-card').forEach(card => card.classList.remove('active'));
                this.closest('.radio-card').classList.add('active');
            });
        });

    /* Submit Form */

    //const form = document.getElementById('multi-step-form');
        
        //form.addEventListener('submit', (event) => {
            //event.preventDefault();
            function submit(){

                const formData = new FormData();

                // Get text field values
                const name = document.getElementById('name').value;
                const description = document.getElementById('description').value;
                const email = document.getElementById('email').value;
                const busPhone = document.getElementById('busPhone').value;
                
                // Check for the 'active' class and retrieve additional fields
                let fromDay = "No value.";
                let toDay = "No value.";
                const div1 = document.getElementById('div1');
                if (div1.classList.contains('active')) {
                    fromDay = document.getElementById('fromDay').value;
                    toDay = document.getElementById('toDay').value;
                }
                
                let oprtDay = "No Value.";
                const div2 = document.getElementById('div2');
                if (div2.classList.contains('active')) {
                    oprtDay = document.getElementById('oprtDay').value;
                }
                
                let fromTime = "00:00";
                let toTime = "00:00";
                const div3 = document.getElementById('div3');
                if (div3.classList.contains('active')) {
                    fromTime = document.getElementById('fromTime').value;
                    toTime = document.getElementById('toTime').value;
                }
                
                let oprtTime = "No value.";
                const div4 = document.getElementById('div4');
                if (div4.classList.contains('active')) {
                    oprtTime = document.getElementById('oprtTime').value;
                }
                
                // Get other fields
                const region = document.getElementById('regionSelect').value;
                //const region = regionSelect.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                const provinceSelect = document.getElementById('provinceSelect').value;
                const province = provinceSelect.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                const municipalitySelect = document.getElementById('municipalitySelect').value;
                const municipality = municipalitySelect.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                const barangaySelect = document.getElementById('barangaySelect').value;
                const barangay = barangaySelect.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' ');
                const sbhNo = document.getElementById('sbhNo').value;
                const latitude = document.getElementById('latitude').value;
                const longitude = document.getElementById('longitude').value;
                const businessType = document.querySelector('input[name="businessType"]:checked').value;
                const firstName = document.getElementById('firstName').value;
                const middleName = document.getElementById('middleName').value;
                const lastName = document.getElementById('lastName').value;
                const suffix = document.getElementById('suffix').value;
                const age = document.getElementById('age').value;
                const role = document.getElementById('role').value;
                const ownrEmail = document.getElementById('ownrEmail').value;
                const ownrPhone = document.getElementById('ownrPhone').value;
                
                // Get the files
                const barangayClearance = document.getElementById('barangayClearance').files[0];
                const businessPermit = document.getElementById('businessPermit').files[0];
                const occupancyCertificate = document.getElementById('occupancyCertificate').files[0];
                const taxCertificate = document.getElementById('taxCertificate').files[0];
                
                // Append all text fields to FormData
                formData.append('name', name);
                formData.append('description', description);
                formData.append('email', email);
                formData.append('busPhone', busPhone);
                formData.append('fromDay', fromDay);
                formData.append('toDay', toDay);
                formData.append('oprtDay', oprtDay);
                formData.append('fromTime', fromTime);
                formData.append('toTime', toTime);
                formData.append('oprtTime', oprtTime);
                formData.append('region', region);
                formData.append('province', province);
                formData.append('municipality', municipality);
                formData.append('barangay', barangay);
                formData.append('sbhNo', sbhNo);
                formData.append('latitude', latitude);
                formData.append('longitude', longitude);
                formData.append('businessType', businessType);
                formData.append('firstName', firstName);
                formData.append('middleName', middleName);
                formData.append('lastName', lastName);
                formData.append('suffix', suffix);
                formData.append('age', age);
                formData.append('role', role);
                formData.append('ownrEmail', ownrEmail);
                formData.append('ownrPhone', ownrPhone);
                
                // Append all files to FormData
                formData.append('file1', barangayClearance);
                formData.append('file2', businessPermit);
                formData.append('file3', occupancyCertificate);
                formData.append('file4', taxCertificate);
                
                // Make the API request with FormData
                fetch('https://bfmsi.smartbarangayconnect.comgit/api-gateway/public/inspection/register', {
                    method: 'POST',
                    body: formData, // No need to manually set 'Content-Type', it's handled by FormData
                })
                .then(response => {
                    if (response.ok){
                        Swal.fire({
                            title: "Store registered!",
                            text: "Store is for verification.",
                            icon: "success",
                            confirmButtonColor: "#3085d6"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var form = document.getElementById("multi-step-form");
                                    form.reset();
                                    var region = document.getElementById("regionSelect");
                                    region.value = ""; // Reset to region option
                                    var province = document.getElementById("provinceSelect");
                                    province.value = ""; // Reset to province option
                                    var municipality = document.getElementById("municipalitySelect");
                                    municipality.value = ""; // Reset to municipality option
                                    var barangay = document.getElementById("barangaySelect");
                                    barangay.value = ""; // Reset to barangay option
                                    const previous = document.getElementById('prev-2');
                                    previous.click();

                                }
                            });
                    }
                })
                .catch(error => {
                    console.error('Error creating item:', error);
                    alert('Error creating item. Please try again.');
                });               
        };
        
    