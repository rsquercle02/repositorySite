<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consumer Feedback</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
  <style>
    /* Page Container */
    .container {
      max-width: 800px;
      background-color: #f9f9f9;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Page Title */
    h1 {
      font-size: 2rem;
      font-weight: bold;
      color: #343a40;
      text-align: center;
      margin-bottom: 20px;
    }

    /* Input Fields */
    .form-control, .form-select {
      border-radius: 5px;
      border: 1px solid #ced4da;
    }

    /* Button Styling */
    .btn-success {
      background-color: #28a745;
      border: none;
    }
    .btn-success:hover {
      background-color: #218838;
    }
    .btn-secondary {
      background-color: #6c757d;
      border: none;
    }

    /* Table Styling */
    #feedbackTable {
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
    }
    #feedbackTable th {
      background-color: #007bff;
      color: #fff;
      text-align: center;
    }
    #feedbackTable td {
      vertical-align: middle;
      text-align: center;
    }
    tbody tr:nth-child(even) {
      background-color: #f2f2f2;
    }

    /* Modal Styling */
    .modal-content {
      border-radius: 10px;
      padding: 20px;
    }
    .modal-body h1 {
      font-size: 1.5rem;
      color: #343a40;
      margin-bottom: 15px;
    }
    .modal-body label {
      font-weight: 500;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <h1>Consumer Feedback</h1>

  <div class="text-end mb-4">
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#feedbackModal">Submit Feedback</button>
  </div>

  <!-- Feedback Table -->
  <div class="border rounded-2 p-3" style="height: 50vh; overflow-y: auto;">
    <table id="feedbackTable" class="table table-striped table-hover">
      <thead>
        <tr>
          <th scope="col">Consumer</th>
          <th scope="col">Market</th>
          <th scope="col">Category</th>
          <th scope="col">Feedback</th>
          <th scope="col">Suggestions/Reports</th>
        </tr>
      </thead>
      <tbody>
        <!-- Dynamic rows will be added here -->
      </tbody>
    </table>
  </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <h1>Submit Your Feedback</h1>
        <form id="feedbackForm">
          <!-- Consumer Name -->
          <div class='mb-3'>
            <label class='form-label' for='consumer-name'>Consumer Name:</label>
            <input class='form-control' type='text' id='consumer-name' required />
          </div>

          <!-- Market Name -->
          <div class='mb-3'>
            <label class='form-label' for='marketName'>Market Name:</label>
            <input class='form-control' type='text' id='marketName' placeholder='Enter Market Name' required />
          </div>

          <!-- Category Dropdown -->
          <div class='mb-3'>
            <label class='form-label' for='categorySelect'>Category:</label>
            <select class='form-select' id='categorySelect' required>
              <option value="" disabled selected>Select Category</option>
              <option value='Meat'>Meat</option>
              <option value='Vegetable'>Vegetable</option>
              <option value='Fish'>Fish</option>
            </select>
          </div>

          <!-- Feedback Textarea -->
          <div class='mb-3'>
            <label class='form-label' for='feedback'>Feedback:</label>
            <textarea class='form-control' id='feedback' required></textarea>
          </div>

          <!-- Suggestions/Reports Textarea -->
          <div class='mb-3'>
            <label class='form-label' for='suggestions'>Suggestions/Reports:</label>
            <textarea class='form-control' id='suggestions' placeholder="Enter suggestions or reports here" required></textarea>
          </div>

          <!-- Submit Buttons -->
          <div class='d-flex justify-content-end'>
            <button class='btn btn-secondary me-3' data-bs-dismiss='modal' type='button'>Close</button>
            <button class='btn btn-success' type='submit'>Submit Feedback</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
// JavaScript functionality
document.addEventListener("DOMContentLoaded", function() {
  
  const feedbackForm = document.getElementById("feedbackForm");
  const feedbackTable = document.getElementById("feedbackTable").querySelector("tbody");

  feedbackForm.addEventListener("submit", function(event) {
    
    event.preventDefault();

    // Get values from the form
    const consumerName = document.getElementById("consumer-name").value; // Consumer Name
    const marketName = document.getElementById("marketName").value; // Market Name
    const category = document.getElementById("categorySelect").value; // Category
    const feedbackText = document.getElementById("feedback").value; // Feedback Text
    const suggestionsText = document.getElementById("suggestions").value; // Suggestions/Reports Text

    // Add new row to feedback table
    const newRow = document.createElement("tr");

     newRow.innerHTML = `
       <td>${consumerName}</td> 
       <td>${marketName}</td> 
       <td>${category}</td> 
       <td>${feedbackText}</td> 
       <td>${suggestionsText}</td> 
     `;
     
     feedbackTable.appendChild(newRow);

     // Reset the form and close the modal
     feedbackForm.reset();
     document.getElementById("feedbackModal").querySelector(".btn-secondary").click();
     
     // Show success alert
     alert("Feedback submitted successfully!");
   });
});
</script>

</body>
</html>
