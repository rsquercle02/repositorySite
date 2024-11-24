<?php
require 'dbConnection.php';

$marketid = $_POST['marketid'];
$marketname = $_POST['marketname'];
$inputviolation = $_POST['inputviolation'];



$folder = "inspections/".$_POST["marketname"];

mkdir($folder, 0755);

list($width, $height) = getimagesize($_FILES["picture"]["tmp_name"]);

$srcImage = imagecreatefromjpeg($_FILES["picture"]["tmp_name"]);

$newHeight = "500";

$newWidth = "500";
    
$destination = imagecreatetruecolor($newWidth, $newHeight);

imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

$randomNumber = mt_rand(100,999);
    
$photo = "inspections/".$_POST["marketname"]."/".$randomNumber.".jpg";

imagejpeg($destination, $photo);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO inspection (market_Id, market_Name, inspection_Photo, market_Violation) VALUES ('$marketid', '$marketname', '$photo', '$violation')";
if ($conn->query($query) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}
$conn->close();

