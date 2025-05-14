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
   
    <!-- CSS -->
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/loginstyle.css">

    <!-- Bootstrap & Icons (Optional but necessary for your footer icons) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <title>Login</title>
</head>

<body>
<div class="container-fluid d-flex flex-column min-vh-100">

<!-- Main Login Form -->
<main class="flex-fill d-flex justify-content-center align-items-center">
  <form id="loginForm">
    <div class="login">
      <h2>Login</h2>
      <div class="inputBx">
        <input type="text" id="email" name="email" placeholder="Email" required>
      </div>
      <div class="inputBx">
        <input type="password" id="password" name="password" placeholder="Password" required>
      </div>
      <div class="inputBx">
        <button type="submit" id="login" class="loginbtn">Log in</button>
      </div>
      <div class="inputBx">
        <div id="responseMessage" class="respmsg"></div>
      </div>
    </div>
  </form>
</main>

<!-- Footer -->
<footer class="footer mt-auto py-3 border-top text-white" style="background-color:rgba(25, 29, 103, 0.5);">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
    
    <!-- Left side: Logo and Text -->
    <div class="d-flex align-items-center mb-2 mb-md-0">
      <a href="/" class="me-2 text-decoration-none">
        <img src="assets/images/unified-lgu-logo.png" alt="Site Logo" class="img-fluid" style="height: 25px;">
      </a>
      <span class="text-white">&copy; 2025 Barangay Food Market Safety</span>
    </div>

    <ul class="list-unstyled mb-0">
    <li class="mb-2">
        <a class="text-white d-flex align-items-center" href="https://bfmsi.smartbarangayconnect.com/portal/concerns">
        <i class="bi bi-chat-fill fs-5 me-2"></i>
        <span>Food Market Concerns</span>
        </a>
    </li>
    <li class="mb-2">
        <a class="text-white" href="https://www.facebook.com/oldcapitolsitemercado/">
        <i class="bi bi-facebook fs-5 me-2"></i>
        <span>Old Capitol Site</span>
        </a>
    </li>
    <li class="mb-2">
        <a class="text-white d-flex align-items-center" href="tel:09123456789">
        <i class="bi bi-telephone-fill fs-5 me-2"></i>
        <span>09562399188</span>
        </a>
    </li>
    </ul>

  </div>
</footer>

</div>


  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/login.js"></script>
</body>
</html>

