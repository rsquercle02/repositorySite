<?php
$servername = "localhost"; // Your server name
$username = "root"; // Your database username
$password = "12345"; // Your database password
$dbname = "new_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
