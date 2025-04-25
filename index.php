<?php
session_start();

if (isset($_SESSION["id"])) {
    header("Location: template.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1885ed">
    <link rel="icon" href="assets/images/unified-lgu-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/loginstyle.css">
    <title>Login</title>
</head>

<body>
    <form id="loginForm">
        <div class="login">
            <h2>Login</h2>
            <div class="inputBx">
                <input type="text" id="email" name="email">
            </div>
            <div class="inputBx">
                <input type="password" id="password" name="password">
            </div>
            <div class="inputBx">
            <button type="submit" id="login" class="loginbtn">Log in</button>
            </div>
            <div class="inputBx">
                <div id="responseMessage" class="respmsg"></div>
            </div>
        </div>
    </form>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="login.js"></script>
</html>
