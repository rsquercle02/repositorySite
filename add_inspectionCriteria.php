<?php
require 'dbConnection.php';

$criteriacategory = $_POST['criteriaCategory'];
$criteriaquestion = $_POST['criteriaQuestion'];


$query = "INSERT INTO inspectioncriteria (category, question) VALUES ('$criteriacategory', '$criteriaquestion')";
if ($conn->query($query) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $query . "<br>" . $conn->error;
}
$conn->close();

