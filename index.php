<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1885ed">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="loginstyle.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="login.js"></script>
    <title>Login</title>
</head>

<body>
    <form id="loginForm">
        <div class="login">
            <h2>Login</h2>
            <div class="inputBx">
                <input type="text" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="inputBx">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="inputBx">
                <input type="submit" name="log in" value="Log in">
            </div>
            <div class="inputBx">
                <div id="responseMessage"></div>
            </div>
            <div class="inputBx">
                <button type="button" onclick="window.location.href='signup.php'" class="btn btn-primary">Sign Up</button>
            </div>
        </div>
    </form>
</body>
</html>
