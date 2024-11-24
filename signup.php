<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body>
        <div class="container w-50">
            <h1 class="text-center">Sign up</h1>
                <form id="userForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label">Fullname:</label>
                        <input type="text" id="fullname" class="form-control" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label for="message-text" class="col-form-label">Username:</label>
                        <input type="text" id="username" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="message-text" class="col-form-label">Password:</label>
                        <input type="password" id="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="message-text" class="col-form-label">Profile:</label>
                        <select id="profile" class="form-select" aria-label="Default select example" name="profile" required>
                            <option disabled>Choose Profile</option>
                            <option value="Vendor"selected>Vendor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="message-text" class="col-form-label">Profile Picture:</label>
                        <input type="file" id="picture" class="newPics" name="picture">
                        <div class="help-block" class="form-text">Maximum size 200Mb</div>
                        <img class="preview" src="person-square.svg" alt="Image Preview" style="width:100px;">
                    </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-success rounded-3">Create</button>
                </div>
            </form>
        </div>
</body>

<script>
    $(document).ready(function() {
        // Add User submit
    $('#userForm').on('submit', function(e) {
      e.preventDefault();
  
        var formData = new FormData(this);
  
        // Add User
        $.ajax({
          url: 'add_userManagement.php',
          type: 'POST',
          data: formData,
          enctype: 'multipart/form-data',
          contentType: false,
          processData: false,
          success: function(response) {
            Swal.fire({
                title: "Create User",
                text: "User created successfully.",
                icon: "success"
            }).then(function() { window.location.href = 'login.php';
            });
            $('#userForm')[0].reset();
            $('.preview').attr('src', '');
          }
        });
      });

      //picture preview
      $('.newPics').on('change', function() {
        var newImage = this.files[0];
        var imgData = new FileReader();
        
        imgData.readAsDataURL(newImage);
        
        imgData.onload = function(event) {
            var routeImg = event.target.result;
            var preview = document.querySelector('.preview');
            preview.src = routeImg;
        };
    });
});
    </script>
</html>