<?php
require "dbConnection.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM userstable WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $credentials = $result->fetch_assoc();
    $hashedPassword = $credentials['password'];

    if (password_verify($password, $hashedPassword)) {
        session_start();
        session_regenerate_id(true);

        $_SESSION["loggedIn"] = "ok";
        $_SESSION["id"] = $credentials["id"];
        $_SESSION["username"] = $credentials["username"];
        $_SESSION["profile"] = $credentials["profile"];
        $_SESSION["picture"] = $credentials["picture"];
        
        // Store IP and user agent in session for session hijacking protection
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        echo "Login successful!";
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>
