<?php
require 'dbConnection.php';

$fullname = $_POST['fullname'];
$username = $_POST['username'];
$password = $_POST['password'];
$profile = $_POST['profile'];


$folder = "users/".$_POST["username"];

mkdir($folder, 0755);

list($width, $height) = getimagesize($_FILES["picture"]["tmp_name"]);

$srcImage = imagecreatefromjpeg($_FILES["picture"]["tmp_name"]);

$newHeight = "500";

$newWidth = "500";
    
$destination = imagecreatetruecolor($newWidth, $newHeight);

imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

$randomNumber = mt_rand(100,999);
    
$photo = "users/".$_POST["username"]."/".$randomNumber.".jpg";

imagejpeg($destination, $photo);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO userstable (fullname, username, password, profile, picture) VALUES ('$fullname', '$username', '$hashedPassword', '$profile', '$photo')";
if ($conn->query($query) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}
$conn->close();

