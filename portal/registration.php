<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Multi-Step Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Form addres -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Css file -->
    <link rel="stylesheet" href="css/registrationstyle.css"/>
</head>
<body>
    <div class="mx-3">
        <div class="bg-white sticky-top">
        <h2 class="pt-3">Inspection Registration</h2>

        <!-- Step Indicator -->
        <div class="progress mb-4" style="height: 30px;">
            <div 
                class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                role="progressbar" 
                id="progress-bar" 
                style="width: 25%;"
                aria-valuenow="25" 
                aria-valuemin="0" 
                aria-valuemax="100">
                Step 1 of 4
            </div>
        </div>
        </div>

        <form id="multi-step-form" enctype="multipart/form-data">
            <!-- Step 1: Business Information -->
            <div class="step active" id="step-1">
                <h4>Step 1: Business Information</h4>
                <div class="mb-3">
                    <label for="name" class="form-label">Business Name:</label>
                    <input type="text" class="form-control" id="name" name="name">
                    <div id="nameErr" class="form-text error"></div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Business Description:</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                    <div id="descriptionErr" class="form-text error"></div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email">
                    <div id="busEmailErr" class="form-text error"></div>
                </div>
                <div class="mb-3">
                    <label for="busPhone" class="form-label">Phone Number:</label>
                    <input type="tel" class="form-control" id="busPhone" name="busPhone">
                    <div id="busPhoneErr" class="form-text error"></div>
                </div>

                <!-- Operating days and hours -->
                <div class="btn-container input-group d-flex mb-3">
                    <div id="groupLabel" class="input-group-text px-1 border-0 rounded-0">Operating Days:</div>
                    <button type="button" id="showDiv1" aria-labelledby="groupLabel" class="btn btn-primary px-1 active">Fixed Day</button>
                    <button type="button" id="showDiv2" aria-labelledby="groupLabel" class="btn btn-primary px-1 rounded-0">Custom Day</button>
                </div>

                <div id="div1" class="switchable active">
                    <div class="input-group">
                    <label class="input-group-text">From:</label>
                    <select id="fromDay" class="form-select" aria-label="Default select example" name="fromDay">
                        <option value="" selected>Choose Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    <label class="input-group-text">To:</label>
                    <select id="toDay" class="form-select" aria-label="Default select example" name="toDay">
                        <option value="" selected>Choose Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    </div>
                <div id="fxDayErr" class="form-text error mb-3"></div>
                </div>

                <div id="div2" class="switchable">
                    <textarea class="form-control" id="oprtDay" name="oprtDay"></textarea>
                <div id="cusDayErr" class="form-text error mb-3"></div>
                </div>

                <div class="btn-container input-group d-flex mb-3">
                    <div id="groupLbl" class="input-group-text px-1 border-0 rounded-0">Operating Hours:</div>
                    <button type="button" id="showDiv3" aria-labelledby="groupLbl" class="btn btn-primary px-1 active">Fixed Hours</button>
                    <button type="button" id="showDiv4" aria-labelledby="groupLbl" class="btn btn-primary px-1 rounded-0">Custom Hours</button>
                </div>

                <div id="div3" class="switchable active">
                    <div class="input-group">
                    <label class="input-group-text">From:</label>
                    <input class="form-control" type="time" id="fromTime" name="fromTime">
                    <label class="input-group-text">To:</label>
                    <input class="form-control" type="time" id="toTime" name="toTime">
                    </div>
                <div id="fxTimeErr" class="form-text error mb-3"></div>
                </div>

                <div id="div4" class="switchable">
                    <textarea class="form-control" id="oprtTime" name="oprtTime"></textarea>
                <div id="cusTimeErr" class="form-text error mb-3"></div>
                </div>
                
                <!-- Business address -->
                <div class="mb-3 mx-0">
                    <label class="fs-5">Business address:</label>
                        <div>
                            <label for="regionSelect" class="col-sm-3 col-form-label">Region</label>
                            <div>
                                <select id="regionSelect" onchange="loadProvinces()" class="form-select">
                                    <option value="">Select Region</option>
                                    <?php
                                    // Read cluster.json and populate the region dropdown
                                    $data = file_get_contents('cluster.json');
                                    $regions = json_decode($data, true);
                                    foreach ($regions as $regionCode => $region) {
                                        echo "<option value='" . $regionCode . "'>" . $region['region_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div id="regionErr" class="form-text error"></div>
                            <label for="provinceSelect" class="col-sm-3 col-form-label">Province</label>
                            <div class="col-lg-12">
                                <select id="provinceSelect" onchange="loadMunicipalities()" class="form-select">
                                    <option value="">Select Province</option>
                                </select>
                            </div>
                        <div id="provinceErr" class="form-text error"></div>
                            <label for="municipalitySelect" class="col-sm-3 col-form-label">Municipality</label>
                            <div class="col-sm-12">
                                <select id="municipalitySelect" onchange="loadBarangays()" class="form-select">
                                    <option value="">Select Municipality</option>
                                </select>
                            </div>
                        <div id="municipalityErr" class="form-text error"></div>
                            <label for="barangaySelect" class="col-sm-3 col-form-label">Barangay</label>
                            <div class="col-sm-12">
                                <select id="barangaySelect" class="form-select">
                                    <option value="">Select Barangay</option>
                                </select>
                            </div>
                        <div id="barangayErr" class="form-text error mb-3"></div>

                        <div class="mb-2">
                            <label for="sbhNo" class="form-label">Street name, Building, House no.</label>
                            <input type="text" class="form-control" id="sbhNo">
                            <div id="sbhErr" class="form-text error mb-3"></div>
                        </div>
                </div>

                <button type="button" class="btn btn-primary mb-3" id="next-1">Next</button>
            </div>

            <!-- Step 2: Business Type -->
            <div class="step" id="step-2">
                <h4>Step 2: Business Type</h4>
                <div class="mb-3">
                <div class="row gy-3">
                    <!-- Option 1 -->
                    <div class="col-md-4">
                        <div class="radio-card p-3 text-center">
                            <input type="radio" id="food-production" name="businessType" value="Food production establishments.">
                            <label for="food-production" class="d-block">
                                <div class="radio-title">Food Production Establishments</div>
                                <ul class="radio-description list-unstyled">
                                <li>Bakeries</li>
                                <li>Pastry shops</li>
                                <li>Confectioneries</li>
                                <li>Ice cream and frozen dessert makers</li>
                                </ul>
                            </label>
                        </div>
                    </div>

                    <!-- Option 2 -->
                    <div class="col-md-4">
                        <div class="radio-card p-3 text-center">
                            <input type="radio" id="food-service" name="businessType" value="Food service establishments.">
                            <label for="food-service" class="d-block">
                                <div class="radio-title">Food Service Establishments</div>
                                <ul class="radio-description list-unstyled">
                                <li>Restaurants (fine dining, casual dining)</li>
                                <li>Fast-food chains</li>
                                <li>Cafés and coffee shops</li>
                                <li>Catering services</li>
                                <li>Food trucks</li>
                                </ul>
                            </label>
                        </div>
                    </div>

                    <!-- Option 3 -->
                    <div class="col-md-4">
                        <div class="radio-card p-3 text-center">
                            <input type="radio" id="retail-food" name="businessType" value="Retail food stores.">
                            <label for="retail-food" class="d-block">
                                <div class="radio-title">Retail Food Stores</div>
                                <ul class="radio-description list-unstyled">
                                <li>Grocery stores and supermarkets</li>
                                <li>Convenience stores</li>
                                <li>Specialty food shops (organic, imported goods)</li>
                                </ul>
                            </label>
                        </div>
                    </div>

                    <!-- Option 4 -->
                    <div class="col-md-4">
                        <div class="radio-card p-3 text-center">
                            <input type="radio" id="beverage-et" name="businessType" value="Beverage establishments.">
                            <label for="beverage-et" class="d-block">
                                <div class="radio-title">Beverage Establishments</div>
                                <ul class="radio-description list-unstyled">
                                <li>Milk tea shops</li>
                                <li>Juice bars and smoothie stands</li>
                                <li>Halo-halo and palamig stalls</li>
                                <li>Coffee shops</li>
                                </ul>
                            </label>
                        </div>
                    </div>

                    <!-- Option 5 -->
                    <div class="col-md-4">
                        <div class="radio-card p-3 text-center">
                            <input type="radio" id="street-vendors" name="businessType" value="Street vendors and small eateries.">
                            <label for="street-vendors" class="d-block">
                                <div class="radio-title">Street Vendors and Small Eateries</div>
                                <ul class="radio-description list-unstyled">
                                <li>Carinderias</li>
                                <li>Street food stalls (grilled, fried, steamed snacks)</li>
                                <li>Palamig and taho vendors</li>
                                </ul>
                            </label>
                        </div>
                    </div>

                    <!-- Option 6 -->
                    <div class="col-md-4">
                        <div class="radio-card p-3 text-center">
                            <input type="radio" id="markets" name="businessType" value="Markets.">
                            <label for="markets" class="d-block">
                                <div class="radio-title">Markets</div>
                                <ul class="radio-description list-unstyled">
                                <li>Wet markets (fresh produce, fish, meat)</li>
                                <li>Flea markets (local food stalls, seasonal items)</li>
                                <li>Farmers markets (organic fruits and vegetables)</li>
                                </ul>
                            </label>
                        </div>
                    </div>
                </div>
                </div>
                <div id="businessTypeErr" class="form-text mb-3 error"></div>

                <button type="button" class="btn btn-secondary mb-3" id="prev-2">Back</button>
                <button type="button" class="btn btn-primary mb-3" id="next-2">Next</button>
            </div>

            <!-- Step 3: Business Representative -->
            <div class="step" id="step-3">
                <h4>Step 3: Business Representative</h4>
                <div class="mb-3">
                    <div id="groupName" class="form-label">Representative Name:</div>
                    <input type="text" aria-labelledby="groupName" class="form-control" id="firstName" name="firstName" placeholder="First Name">
                    <div id="firstNameErr" class="form-text mb-3 error"></div>
                    <input type="text" aria-labelledby="groupName" class="form-control" id="middleName" name="middleName" placeholder="Middle Name">
                    <div id="middleNameErr" class="form-text mb-3 error"></div>
                    <input type="text" aria-labelledby="groupName" class="form-control" id="lastName" name="lastName" placeholder="Last Name">
                    <div id="lastNameErr" class="form-text mb-3 error"></div>
                    <input type="text" aria-labelledby="groupName" class="form-control mb-3" id="suffix" name="suffix" placeholder="Suffix">
                </div>
                <div class="mb-3">
                    <label for="skills" class="form-label">Age:</label>
                    <input type="text" class="form-control" id="age" name="age">
                    <div id="ageErr" class="form-text mb-3 error"></div>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Role in business:</label>
                    <input type="text" class="form-control" id="role" name="role">
                    <div id="roleErr" class="form-text mb-3 error"></div>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="ownrEmail" name="ownrEmail">
                    <div id="ownrEmailErr" class="form-text mb-3 error"></div>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Phone Number:</label>
                    <input type="tel" class="form-control" id="ownrPhone" name="ownrPhone">
                    <div id="ownrPhoneErr" class="form-text mb-3 error"></div>
                </div>
                <button type="button" class="btn btn-secondary mb-3" id="prev-3">Back</button>
                <button type="button" class="btn btn-primary mb-3" id="next-3">Next</button>
            </div>

            <!-- Step 4: Compliance Certificates -->
            <div class="step" id="step-4">
                <h4>Step 4: Compliance Certificates</h4>
                <div class="mb-3">
                    <label for="barangay-clearance" class="form-label">Barangay Clearance:</label>
                    <input type="file" id="barangayClearance" class="form-control" name="barangayClearance">
                    <div id="bClNoErr" class="form-text error"></div>
                    <div id="bClTypeErr" class="form-text error"></div>
                    <div id="bClSizeErr" class="form-text error"></div>
                </div>

                <div class="mb-3">
                    <label for="business-permit" class="form-label">Business Permit:</label>
                    <input type="file" id="businessPermit" class="form-control" name="businessPermit">
                    <div id="bPrNoErr" class="form-text error"></div>
                    <div id="bPrTypeErr" class="form-text error"></div>
                    <div id="bPrSizeErr" class="form-text error"></div>
                </div>

                <div class="mb-3">
                    <label for="occupancy-certificate" class="form-label">Certificate of Occupancy or Rental:</label>
                    <input type="file" id="occupancyCertificate" class="form-control" name="occupancyCertificate">
                    <div id="oCrNoErr" class="form-text error"></div>
                    <div id="oCrTypeErr" class="form-text error"></div>
                    <div id="oCrSizeErr" class="form-text error"></div>
                </div>

                <div class="mb-3">
                    <label for="tax-certificate" class="form-label">Tax Certificate:</label>
                    <input type="file" id="taxCertificate" class="form-control" name="taxCertificate">
                    <div id="tCrNoErr" class="form-text error"></div>
                    <div id="tCrTypeErr" class="form-text error"></div>
                    <div id="tCrSizeErr" class="form-text error"></div>
                </div>

                <button type="button" class="btn btn-secondary mb-3" id="prev-4">Back</button>
                <button type="button" class="btn btn-primary mb-3" id="submit">Submit</button>
            </div>
        </form>
    </div>

    <!-- Javascript file -->
    <script src="js/registrationjavascript.js"></script>

    <!-- Form address -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#regionSelect').select2();
            $('#provinceSelect').select2();
            $('#municipalitySelect').select2();
            $('#barangaySelect').select2();
        });
    </script>
    <script>
        function loadProvinces() {
            var regionCode = $('#regionSelect').val();
            $('#provinceSelect').empty().append('<option value="">Select Province</option>');
            $('#municipalitySelect').empty().append('<option value="">Select Municipality</option>');
            $('#barangaySelect').empty().append('<option value="">Select Barangay</option>');
            if (regionCode !== "") {
                var provinceList = <?php echo json_encode($regions); ?>;
                var provinceObj = provinceList[regionCode]['province_list'];
                $.each(provinceObj, function(provinceName, municipalityList) {
                    $('#provinceSelect').append("<option value='" + provinceName + "'>" + provinceName + "</option>");
                });
            }
        }

        function loadMunicipalities() {
            var provinceName = $('#provinceSelect').val();
            $('#municipalitySelect').empty().append('<option value="">Select Municipality</option>');
            $('#barangaySelect').empty().append('<option value="">Select Barangay</option>');
            if (provinceName !== "") {
                var regionCode = $('#regionSelect').val();
                var municipalityList = <?php echo json_encode($regions); ?>;
                var municipalityObj = municipalityList[regionCode]['province_list'][provinceName]['municipality_list'];
                $.each(municipalityObj, function(municipalityName, barangayList) {
                    $('#municipalitySelect').append("<option value='" + municipalityName + "'>" + municipalityName + "</option>");
                });
            }
        }

        function loadBarangays() {
            var municipalityName = $('#municipalitySelect').val();
            $('#barangaySelect').empty().append('<option value="">Select Barangay</option>');
            if (municipalityName !== "") {
                var regionCode = $('#regionSelect').val();
                var provinceName = $('#provinceSelect').val();
                var barangayList = <?php echo json_encode($regions); ?>;
                var barangayArr = barangayList[regionCode]['province_list'][provinceName]['municipality_list'][municipalityName]['barangay_list'];
                $.each(barangayArr, function(index, barangayName) {
                    $('#barangaySelect').append("<option value='" + barangayName + "'>" + barangayName + "</option>");
                });
            }
        }
    </script>
    
</body>
</html>
