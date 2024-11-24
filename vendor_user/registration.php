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
        <h1>Market registration</h1>
        <form id="marketRegistration" enctype="multipart/form-data">
            <input class="form-control my=-3 rounded-3" type="text" id="marketNameInput" name="marketName" placeholder="Enter Market Name" required>
            <input class="form-control my-3 rounded-3" type="text" id="marketLocationInput" name="marketLocation" placeholder="Enter Market Location" required>
            <select class="form-select my-3" id="marketCategoryInput" name="marketCategory" required>
                <option selected disabled> Food Category</option>
                <option value="Fish">Fish</option>
                <option value="Meat">Meat</option>
                <option value="Vegetable">Vegetable</option>
                <option value="Fruits">Fruits</option>
                <option value="Cooked Food">Cooked Food</option>
            </select>
            <label for="message-text" class="col-form-label my-3">Profile Picture:</label>
            <input type="file" id="picture" class="newPics" name="picture">
            <div class="my-3">
            <img class="preview" src="person-square.svg" alt="Image Preview" style="width:100px;">
            </div>
            <div class="m-3">
            <button class="btn btn-success rounded-3" type="submit" name="submit">Add Market</button>
            </div>
        </form>
    </div>
</body>
    <script>
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

      
    </script>
</html>


