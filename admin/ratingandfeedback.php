<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Feedback Collection</title>
</head>
<body>

<div class="container bg-white text-dark rounded-3 shadow mt-3 p-3">
        
        <h1>Market Feedback</h1>
        <div class="row">
        <div class="col-sm-4 col-md-6 col-lg-3">
        <input class="form-control mb-3 lg-m-3 rounded-3" type="text" id="marketName" placeholder="Enter Market Name">
        </div>
        <div class="col-sm-4 col-md-3 col-lg-3">
        <select class="form-select mb-3 lg-m-3" aria-label="Default select example">
            <option selected>Category</option>
            <option value="1">Fish</option>
            <option value="2">Meat</option>
            <option value="3">Vegetable</option>
        </select>
        </div>
        <div class="d-flex justify-content-end col">
            <button class="btn btn-success mb-3 lg-m-3 rounded-3" data-bs-toggle="modal" data-bs-target="#exampleModal">Submit Feedback</button>
        </div>
        </div>
        <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
            <table id="userTable" class="table table-hover">
            <thead>
            <tr>
            <th scope="col">Market</th>
            <th scope="col">Category</th>
            <th scope="col">Feedback</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            </table>
        </div>

    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-body">
        
      <div class="container bg-white text-dark rounded-3 px-0">
        <div class="d-flex justify-content-center">
        <h1>Inspector Feedback</h1>
        </div>
        
        <form id="feedback-form">
            <div class="">
                <label class="form-label" for="market-name">Market Name:</label>
                <input class="form-control mb-3 rounded-3" type="text" id="market-name" required>
            </div>
            <div class="">
                <label class="form-label" for="user-name">Your Name:</label>
                <input class="form-control mb-3 rounded-3" type="text" id="user-name" required>
            </div>
            <div class="">
                <label class="form-label" for="user-feedback">Feedback:</label>
                <textarea class="form-control mb-3 rounded-3" id="user-feedback" required></textarea>
            </div>
            <div class="">
                <label class="form-label" for="user-rating">Rating:</label>
                <select class="form-select mb-3" id="user-rating" required>
                    <option value="">Select a rating</option>
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>
            </div>
            <div class="d-flex justify-content-end">
            <button class="btn btn-secondary m-3" data-bs-dismiss="modal" type="button">Close</button>
            <button class="btn btn-success my-3 rounded-3" type="submit">Submit Feedback</button>
            </div>
        </form>
    </div>
      </div>

    </div>
  </div>
</div>


    <!--<div class="container w-50 bg-white text-dark rounded-3 shadow my-3 p-3">
        <div class="d-flex justify-content-center">
        <h1>Inspector Feedback</h1>
        </div>
        
        <form id="feedback-form">
            <div class="">
                <label class="form-label" for="market-name">Market Name:</label>
                <input class="form-control mb-3 rounded-3" type="text" id="market-name" required>
            </div>
            <div class="">
                <label class="form-label" for="user-name">Your Name:</label>
                <input class="form-control mb-3 rounded-3" type="text" id="user-name" required>
            </div>
            <div class="">
                <label class="form-label" for="user-feedback">Feedback:</label>
                <textarea class="form-control mb-3 rounded-3" id="user-feedback" required></textarea>
            </div>
            <div class="">
                <label class="form-label" for="user-rating">Rating:</label>
                <select class="form-select mb-3" id="user-rating" required>
                    <option value="">Select a rating</option>
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>
            </div>
            <div class="d-flex justify-content-end">
            <button class="btn btn-success m-3 rounded-3" type="submit">Submit Feedback</button>
            </div>

    
        </form>
        
    </div>-->

    <script src="Feedback.js"></script>
</body>
</html>