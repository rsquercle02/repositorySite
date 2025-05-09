<?php
require "dbConnection.php";

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Include PHPMailer via Composer

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT 
        ai.account_id, 
        CONCAT(pi.first_name, ' ', pi.middle_name, ' ', pi.last_name) AS full_name, 
        ai.email,
        ai.password, 
        ai.user_type, 
        ai.barangay_role, 
        ai.status, 
        ai.picture
        FROM accountinformation ai
        JOIN personalinformation pi
        ON pi.personal_id = ai.personal_id
        WHERE ai.email = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $credentials = $result->fetch_assoc();
    //$hashedPassword = $result['passowrd'];

    // Check if any user was found
    if ($result->num_rows === 0) {
        // Username (or email) not found
        echo "Username/email not found.";
        // You can handle this case by returning or performing other actions
    } else {
        // Username (or email) found, fetch the password
        //$credentials = $result->fetch_assoc();
        $hashedPassword = $credentials['password'];
        // Proceed with password verification and other logic

        if (password_verify($password, $hashedPassword)) {
            session_start();
            //session_regenerate_id(true);

            $_SESSION["loggedIn"] = "ok";
            $_SESSION["id"] = $credentials['account_id'];
            $_SESSION["fullname"] = $credentials["full_name"];
            $_SESSION["email"] = $credentials["email"];
            $_SESSION["profile"] = $credentials['user_type'];
            $_SESSION["barangayRole"] = $credentials['barangay_role'];
            $_SESSION["status"] = $credentials["status"];
            $_SESSION["picture"] = $credentials["picture"];
            
            // Store IP and user agent in session for session hijacking protection
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            echo "Login successful!";
        } else {
            echo "Invalid username or password.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
