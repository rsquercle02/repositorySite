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
            $_SESSION["username"] = $credentials["email"];
            $_SESSION["profile"] = $credentials['user_type'];
            $_SESSION["barangayRole"] = $credentials['barangay_role'];
            $_SESSION["status"] = $credentials["status"];
            $_SESSION["picture"] = $credentials["picture"];
            
            // Store IP and user agent in session for session hijacking protection
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            // Function to send OTP email
            function sendOTP($toEmail, $otp) {
                $mail = new PHPMailer(true);  // Create PHPMailer object

                try {
                    // Server settings
                    $mail->isSMTP();                                      // Use SMTP
                    $mail->Host = 'smtp.gmail.com';                        // Set the SMTP server to Gmail
                    $mail->SMTPAuth = true;                               // Enable SMTP authentication
                    $mail->Username = 'bfmsismartbarangayconnect@gmail.com';             // SMTP username (your email address)
                    $mail->Password = 'zgwzenszlzjfakkb';               // SMTP password (your email password or app password)
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Enable TLS encryption
                    $mail->Port = 587;                                    // TCP port to connect to

                    // Recipients
                    $mail->setFrom('bfmsismartbarangayconnect@gmail.com', 'BFMS');
                    $mail->addAddress($toEmail);  // Add recipient's email

                    // Custom message + OTP
                    $mail->isHTML(true);  // Set email format to HTML
                    $mail->Subject = 'Your OTP Code';
                    $mail->Body    = "
                        <h3>Dear User,</h3>
                        <p>Thank you for logging in. To complete your login process, please use the OTP code below:</p>
                        <p><strong>Your OTP code is:</strong> <b>$otp</b></p>
                        <p>This code is valid for 10 minutes. If you did not request this, please ignore this message.</p>
                        <p>Best Regards,<br>Barangay Food Market Safety</p>
                    ";

                    // Send the email
                    $mail->send();
                    echo 'OTP has been sent to your email address.';
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }

            // Generate OTP
            $otp = rand(100000, 999999);  // Generate a 6-digit OTP
            $otp_expiration = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // Store OTP in the database temporarily
            $stmt = $conn->prepare("UPDATE accountinformation SET otp = ?, otp_expiration = ? WHERE account_id = ?");
            $stmt->bind_param("ssi", $otp, $otp_expiration, $_SESSION['id']);
            $stmt->execute();

            $email = $_SESSION['username'];
            // Send OTP to the user's email using PHPMailer
            //sendOTP($email, $otp);

            echo "Login successful!";
        } else {
            echo "Invalid username or password.";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
