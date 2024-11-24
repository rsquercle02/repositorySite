<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

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
    </div>

    <div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Inspection form</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <form id="inspectionForm">
                    <input class="form-control my-3 rounded-3" type="text" id="marketid" name="marketid" placeholder="Market Id" readonly>
                    <input class="form-control my-3 rounded-3" type="text" id="marketname" name="marketname" placeholder="Market Name" readonly>
                    <input class="form-control my-3 rounded-3" type="text" id="marketlocation" name="marketlocation" placeholder="Market Location" readonly>
                    <select class="form-select" id="marketcategory" name="marketcategory" disabled>
                        <option selected disabled>Category</option>
                        <option value="Fish" readonly>Fish</option>
                        <option value="Meat" readonly>Meat</option>
                        <option value="Vegetable" readonly>Vegetable</option>
                        <option value="Fruits" readonly>Fruits</option>
                        <option value="Cooked Food" readonly>Cooked Foods</option>
                    </select>
                    <input class="form-control my-3 rounded-3" type="date" id="inspectiondate" name="inspectiondate" readonly>
                    <input class="form-control my-3 rounded-3" type="time" id="inspectiontime" name="inspectiontime" readonly>
                    <label for="message-text" class="col-form-label my-3">Profile Picture:</label>
                    <input type="file" id="picture" class="newPics" name="picture">
                    <div class="my-3">
                    <img class="preview" src="person-square.svg" alt="Image Preview" style="width:100px;">
                    </div>
                    <label for="message-text" class="col-form-label my-3">Violations:</label>
                    <textarea class="form-control my-3 rounded-3" type="textarea" id="inputviolation" name="inputviolation" required></textarea>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit" class="btn btn-success rounded-3">Submit</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<script>

fetchschedule();

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
      
      // Add inspection schedule form
    $(document).on('click', '.edit-btn', function() {
      const marketid = $(this).data('marketid');
      const marketname = $(this).data('marketname');
      const marketlocation = $(this).data('marketlocation');
      const marketcategory = $(this).data('marketcategory');
      const inspectiondate = $(this).data('inspectiondate');
      const inspectiontime = $(this).data('inspectiontime');

      $('#marketid').val(marketid);
      $('#marketname').val(marketname);
      $('#marketlocation').val(marketlocation);
      $('#marketcategory').val(marketcategory);
      $('#inspectiondate').val(inspectiondate);
      $('#inspectiontime').val(inspectiontime);

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

        $('#inspectionForm').on('submit', function(e) {
      e.preventDefault();
  
        var formData = new FormData(this);
  
        // Add User
        $.ajax({
          url: 'add_inspection.php',
          type: 'POST',
          data: formData,
          enctype: 'multipart/form-data',
          contentType: false,
          processData: false,
          success: function(response) {
            Swal.fire({
                title: "Inspection",
                text: "Inspection successfull.",
                icon: "success"
            });
            $('#inspectionForm')[0].reset();
            $('.preview').attr('src', '');
          }
        });
      });

</script>
</html>