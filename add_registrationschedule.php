<?php

require 'dbConnection.php';

$marketid = $_POST['marketid'];
$marketname = $_POST['marketname'];
$marketlocation = $_POST['marketlocation'];
$marketcategory = $_POST['marketcategory'];
$inspectiondate = $_POST['inspectiondate'];
$inspectiontime = $_POST['inspectiontime'];

$query = "INSERT INTO inspectionschedule (market_Id, market_Name, market_Location, market_Category, inspection_date, inspection_time) VALUES ('$marketid', '$marketname', '$marketlocation', '$marketcategory', '$inspectiondate', '$inspectiontime')";
if ($conn->query($query) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}
$conn->close();
?>