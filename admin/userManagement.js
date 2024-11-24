$(document).ready(function() {
    // Fetch users on page load
    fetchUsers();
  
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
            fetchUsers();
            Swal.fire({
                title: "Create User",
                text: "User created successfully.",
                icon: "success"
            });
            $('#userForm')[0].reset();
          }
        });
      });
  
    //Edit User submit
    $('#editForm').on('submit', function(e) {
      e.preventDefault();
      
      var formData = new FormData(this);
  
  
      $.ajax({
          url: 'update_userManagement.php',
          type: 'POST',
          data: formData,
          enctype: 'multipart/form-data',
          contentType: false,
          processData: false,
          success: function(response) {
            fetchUsers();
            Swal.fire({
                title: "Update User",
                text: "User data updated successfully.",
                icon: "success"
              });
            $('#editForm')[0].reset();
          }
        });
      });
  
    // Edit User button
    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      const fullname = $(this).data('fullname');
      const username = $(this).data('username');
      const password = $(this).data('password');
      const profile = $(this).data('profile');
      const picture = $(this).data('picture');

      $('#editId').val(id);
      $('#editFullname').val(fullname);
      $('#editUsername').val(username);
      $('#editProfile').val(profile);
      $('#editPicture').val(picture);

    });
  
    // Delete User
    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
  
      $.ajax({
        url: 'delete_userManagement.php',
        type: 'POST',
        data: { id },
        success: function(response) {
          fetchUsers();
        }
      });
    });
    
    // Fetch Users
    function fetchUsers() {
      $.ajax({
        url: 'fetch_userManagement.php',
        type: 'GET',
        success: function(response) {
          $('#userTable tbody').html(response);
        }
      });
    }
  
    //Search Users
  
      $('#searchTerm').on('keyup', function() {
          var searchTerm = $(this).val();
  
          $.ajax({
              url: 'search_userManagement.php',
              type: 'GET',
              data: { searchTerm: searchTerm },
              success: function(response) {
                  $('#userTable tbody').html(response);
              }
          });
      });
  
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
  