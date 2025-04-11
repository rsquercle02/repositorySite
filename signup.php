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
                <label for="firstname" class="col-form-label">Firstname:</label>
                <input type="text" id="firstname" class="form-control" name="firstname" required>
            </div>
            <div id="firstnameErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <label for="middlename" class="col-form-label">Middlename:</label>
                <input type="text" id="middlename" class="form-control" name="middlename" required>
            </div>
            <div id="middlenameErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <label for="lastname" class="col-form-label">Lastname:</label>
                <input type="text" id="lastname" class="form-control" name="lastname" required>
            </div>
            <div id="lastnameErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label><br>
                <input class="form-check-input" type="radio" id="male" name="gender" value="male">
                <label class="form-check-label" for="male">Male</label>
                <input class="form-check-input" type="radio" id="female" name="gender" value="female">
                <label class="form-check-label" for="female">Female</label>
            </div>
            <div id="genderErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <label for="username" class="col-form-label">Username:</label>
                <input type="text" id="username" class="form-control" name="username" required>
            </div>
            <div id="usernameErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <label for="password" class="col-form-label">Password:</label>
                <input type="password" id="password" class="form-control" name="password" required>
            </div>
            <div id="passwordErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <label for="picture" class="col-form-label">Profile Picture:</label>
                <input type="file" id="picture" class="newPics" name="picture"><br>
                <img class="preview" src="person-square.svg" alt="Image Preview" style="width:100px;">
            </div>
            <div id="pictureErr" class="form-text error mb-3"></div>

            <div class="mb-3">
                <button type="submit" name="submit" class="btn btn-success rounded-3">Create</button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // Add User submit
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                
                // Get selected gender
                var gender = $('input[name="gender"]:checked').val();
                
                var formData = new FormData(this);
                formData.append('gender', gender);  // Append the selected gender value

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
                        }).then(function() {
                            window.location.href = 'login';
                        });
                        $('#userForm')[0].reset();
                        $('.preview').attr('src', '');
                    }
                });
            });

            // Picture preview
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
</body>
</html>
