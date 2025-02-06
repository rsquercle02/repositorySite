<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <link rel="stylesheet" href="verificationstyle.css">
</head>
<body>
    <div class="container mb-3 shadow p-3">
        <h1>Businesses</h1>
        <div class="col-sm-4 col-md-3 col-lg-3">
            <input class="form-control my-3 rounded-3" type="text" id="searchMarket" placeholder="Search Market">
        </div>
        <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
        <table id="marketsTable" class="table table-hover">
        <thead>
        <tr class="sticky-top">
        <th scope="col">Id</th>
        <th scope="col">Name</th>
        <th scope="col">Description</th>
        <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
    </div>

    <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Verification</h1>
                    <button type="button" id="closeBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalContent">
                        <div class="align">
                        <h3 id="businessName">Capstone Store</h3>
                        <label id="hiddenLabel" hidden></label>
                        <div class="mb-2">
                        <p class="inline">Type:</p> <p id="businessType" class="store-type inline">Food Service Establishments</p>
                        </div>
                        </div>
                        <h5>Barangay Clearance</h5>
                        <div class="mb-2" id="barangayClearance"></div>
                        <h5>Business Permit</h5>
                        <div class="mb-2 embedcon" id="businessPermit"></div>
                        <h5>Occupancy Certificate</h5>
                        <div class="mb-2" id="occupancyCertificate"></div>
                        <h5>Tax Certificate</h5>
                        <div class="mb-2" id="taxCertificate"></div>
                    </div>
                </div>
                    <div class="modal-footer">
                        <button type="button" id="denyBtn" class="btn btn-secondary">Deny</button>
                        <button type="button" id="approveBtn" class="btn btn-success rounded-3">Approve</button>
                    </div>
            </div>
        </div>
    </div>

</body>

<script src="verificationjavascript.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</html>