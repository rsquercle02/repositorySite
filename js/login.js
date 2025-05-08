$(document).ready(function() {

  $(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: 'login_process.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                 $('#responseMessage').html(response);
                if (response.includes("Login successful")) {
                    window.location.href = "template.php";
                } 
            },
            error: function(xhr, status, error) {
                $('#responseMessage').html("An error occurred: " + error);
                console.log(error);
            }
        });
    });
});

});