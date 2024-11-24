<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Markets</title>
    <script src="schedule.js"></script>
</head>
<body>

    <div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
        <h1>Rating and Feedback</h1>
        <div class="col-sm-4 col-md-3 col-lg-3">
            <input class="form-control my-3 rounded-3" type="text" id="searchMarket" placeholder="Search Market">
        </div>
        <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
        <table id="marketsTable" class="table table-hover">
        <thead>
        <tr>
        <th scope="col">Id</th>
        <th scope="col">Name</th>
        <th scope="col">Location</th>
        <th scope="col">Category</th>
        <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
    </div>

    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Rating and Feedback</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="scheduleForm">
                    <input class="form-control my-3 rounded-3" type="text" id="marketid" name="marketid" placeholder="Market Id" readonly>
                    <input class="form-control my-3 rounded-3" type="text" id="marketname" name="marketname" placeholder="Market Name" readonly>
                    <input class="form-control my-3 rounded-3" type="text" id="marketlocation" name="marketlocation" placeholder="Market Location" readonly>
                    <select class="form-select my-3" id="marketcategory" name="marketcategory" disabled>
                        <option selected disabled>Category</option>
                        <option value="Fish" readonly>Fish</option>
                        <option value="Meat" readonly>Meat</option>
                        <option value="Vegetable" readonly>Vegetable</option>
                        <option value="Fruits" readonly>Fruits</option>
                        <option value="Cooked Food" readonly>Cooked Food</option>
                    </select>
                    <select class="form-select my-3" id="inspectorrating" name="inspectorrating">
                        <option selected>Rating</option>
                        <option value="Five star">⭐⭐⭐⭐⭐</option>
                        <option value="Four star">⭐⭐⭐⭐</option>
                        <option value="Three star">⭐⭐⭐</option>
                        <option value="Two star">⭐⭐</option>
                        <option value="One star">⭐</option>
                    </select>
                    <textarea class="form-control my-3 rounded-3" type="textarea" id="inspectorfeedback" name="inspectorfeedback" placeholder="Feedback" required></textarea>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit" class="btn btn-success rounded-3">Add</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
    <h1>Inspection Schedule</h1>
    <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
    <table id="scheduleTable" class="table table-hover">
    <thead>
    <tr>
      <th scope="col">Schedule Id</th>
      <th scope="col">Name</th>
      <th scope="col">Date</th>
      <th scope="col">Time</th>
      <th scope="col">Actions</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
    </table>
    </div>

    
    <script>
        /* document.addEventListener('DOMContentLoaded', () => {
            const applicationHistoryTable = document.querySelector('#applicationHistoryTable tbody');
            const scheduleTable = document.querySelector('#scheduleTable tbody');
            
            const inspectionApplicationForm = document.getElementById('inspectionApplication');
            const inspectionScheduleForm = document.getElementById('inspectionScheduleForm');
            
            const marketApplications = JSON.parse(localStorage.getItem('marketApplications')) || [];

            function updateApplicationHistoryTable() {
                applicationHistoryTable.innerHTML = '';
                marketApplications.forEach((market) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${market.name}</td>
                        <td>${market.category}</td>
                        <td>${market.location}</td>
                    `;
                    applicationHistoryTable.appendChild(row);
                });
            }

            function updateScheduleTable(market, date, time) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${market.name}</td>
                    <td>${market.category}</td>
                    <td>${market.location}</td>
                    <td>${date}</td>
                    <td>${time}</td>
                `;
                scheduleTable.appendChild(row);
            }

            inspectionApplicationForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const name = document.getElementById('marketNameInput').value;
                const location = document.getElementById('marketLocationInput').value;
                const category = document.getElementById('marketCategoryInput').value;

                const newMarketApplication = { name, location, category };
                marketApplications.push(newMarketApplication);
                localStorage.setItem('marketApplications', JSON.stringify(marketApplications));
                
                updateApplicationHistoryTable();
                inspectionApplicationForm.reset();
            });

            inspectionScheduleForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const name = document.getElementById('scheduleMarketName').value;
                const location = document.getElementById('scheduleMarketLocation').value;
                const category = document.getElementById('scheduleMarketCategory').value;
                const date = document.getElementById('inspectionDate').value;
                const time = document.getElementById('inspectionTime').value;

                updateScheduleTable({ name, location, category }, date, time);
                inspectionScheduleForm.reset();
            });

            updateApplicationHistoryTable();
        }); */

        fetchschedule();

        $('#marketRegistration').on('submit', function(e) {
      e.preventDefault();
  
        var formData = new FormData(this);
  
        // Add Market
        $.ajax({
          url: 'add_registration.php',
          type: 'POST',
          data: formData,
          enctype: 'multipart/form-data',
          contentType: false,
          processData: false,
          success: function(response) {
            Swal.fire({
                title: "Market registration",
                text: "Market registered successfully.",
                icon: "success"
            });
            $('#marketRegistration')[0].reset();
            $('.preview').attr('src', '');
          }
        });
        

        $('.newPics').on('change', function() {
        var newImage = this.files[0];
        var imgData = new FileReader();
        
        imgData.readAsDataURL(newImage);
        
        imgData.onload = function(event) {
            var routeImg = event.target.result;
            var preview = document.querySelector('.preview');
            preview.src = routeImg;
        }
        });

      });

      //search markets
      $('#searchMarket').on('keyup', function() {
          var searchMarket = $(this).val();
  
          $.ajax({
              url: 'search_MarketsInspector.php',
              type: 'GET',
              data: { searchMarket: searchMarket },
              success: function(response) {
                  $('#marketsTable tbody').html(response);
              }
          });
      });

      // Add inspection schedule form
    $(document).on('click', '.edit-btn', function() {
      const marketid = $(this).data('marketid');
      const marketname = $(this).data('marketname');
      const marketlocation = $(this).data('marketlocation');
      const marketcategory = $(this).data('marketcategory');

      $('#marketid').val(marketid);
      $('#marketname').val(marketname);
      $('#marketlocation').val(marketlocation);
      $('#marketcategory').val(marketcategory);
    });

      // Add inspection schedule
    $('#scheduleForm').on('submit', function(e) {
        $('#marketcategory').prop('disabled', false);
      e.preventDefault();
  
        var formData = new FormData(this);
  
        // Add rating and feedback
        $.ajax({
          url: 'add_ratingAndFeedback.php',
          type: 'POST',
          data: formData,
          enctype: 'multipart/form-data',
          contentType: false,
          processData: false,
          success: function(response) {
            Swal.fire({
                title: "Rating and Feedback",
                text: "Created successfully.",
                icon: "success"
            });
            $('#scheduleForm')[0].reset();
            $('#scheduleModal').modal('hide');
            $('#marketcategory').prop('disabled', true);
            fetchschedule();
          }
        });
      });

      // Fetch inspection schedule
    function fetchschedule() {
      $.ajax({
        url: 'fetch_inspectionSchedule.php',
        type: 'GET',
        success: function(response) {
          $('#scheduleTable tbody').html(response);
        }
      });
    }
    </script>
</body>
</html>


