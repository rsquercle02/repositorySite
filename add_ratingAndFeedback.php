<?php

require 'dbConnection.php';

$marketid = $_POST['marketid'];
$marketname = $_POST['marketname'];
$marketlocation = $_POST['marketlocation'];
$marketcategory = $_POST['marketcategory'];
$inspectorrating = $_POST['inspectorrating'];
$inspectorfeedback = $_POST['inspectorfeedback'];

$query = "INSERT INTO ratingandfeedback (market_Id, market_Name, market_Location, market_Category, inspector_Rating, inspector_Feedback) VALUES ('$marketid', '$marketname', '$marketlocation', '$marketcategory', '$inspectorrating', '$inspectorfeedback')";
if ($conn->query($query) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}
$conn->close();
?>