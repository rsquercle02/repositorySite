<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
        <h1>Inspection Criteria</h1>
        <form id="criteriaForm" enctype="multipart/form-data">
            <select class="form-select my-3" id="criteriaCategory" name="criteriaCategory" required>
                <option selected disabled>Criteria Category</option>
                <option value="Cleanliness">Cleanliness</option>
                <option value="Safety">Safety</option>
                <option value="Appearance">Appearance</option>
            </select>
            <input class="form-control my=-3 rounded-3" type="text" id="criteriaQuestion" name="criteriaQuestion" placeholder="Enter question" required>
            <div class="m-3">
            <button class="btn btn-success rounded-3" type="submit" name="submit">Add Question</button>
            </div>
        </form>
    </div>

    <div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
    <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
    <table id="criteriaTable" class="table table-hover">
    <thead>
    <tr>
      <th scope="col">Criteria Id</th>
      <th scope="col">Category</th>
      <th scope="col">Question</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
    </table>
    </div>
    </div>
</body>
<script>
    fetchQuestions();

    $('#criteriaForm').on('submit', function(e) {
      e.preventDefault();
  
        var formData = new FormData(this);
  
        // Add User
        $.ajax({
          url: 'add_inspectionCriteria.php',
          type: 'POST',
          data: formData,
          enctype: 'multipart/form-data',
          contentType: false,
          processData: false,
          success: function(response) {
            Swal.fire({
                title: "Inspection Criteria",
                text: "Criteria created successfully.",
                icon: "success"
            });
            $('#criteriaForm')[0].reset();
            fetchQuestions();
          }
        });
      });

      // Delete Criteria
    $(document).on('click', '.delete-btn', function() {
      const criteria_Id = $(this).data('criteria_id');
  
      $.ajax({
        url: 'delete_criteriaManagement.php',
        type: 'POST',
        data: { criteria_Id },
        success: function(response) {
          fetchQuestions();
        }
      });
    });

      // Fetch Questions
    function fetchQuestions() {
      $.ajax({
        url: 'fetch_criteriaManagement.php',
        type: 'GET',
        success: function(response) {
          $('#criteriaTable tbody').html(response);
        }
      });
    }
      </script>
</html>