<?php

require 'dbConnection.php';

$marketname = $_POST['marketName'];
$marketlocation = $_POST['marketLocation'];
$marketcategory = $_POST['marketCategory'];



$folder = "markets/".$_POST["marketName"];

mkdir($folder, 0755);

list($width, $height) = getimagesize($_FILES["picture"]["tmp_name"]);

$srcImage = imagecreatefromjpeg($_FILES["picture"]["tmp_name"]);

$newHeight = "500";

$newWidth = "500";
    
$destination = imagecreatetruecolor($newWidth, $newHeight);

imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

$randomNumber = mt_rand(100,999);
    
$photo = "markets/".$_POST["marketName"]."/".$randomNumber.".jpg";

imagejpeg($destination, $photo);

$query = "INSERT INTO marketlist (market_Name, market_Location, market_Category, market_Logo) VALUES ('$marketname', '$marketlocation', '$marketcategory', '$photo')";
if ($conn->query($query) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}
$conn->close();
?>