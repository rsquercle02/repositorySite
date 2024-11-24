<?php
require 'dbConnection.php';

$id = $_POST['criteria_Id'];

$query = "DELETE FROM inspectioncriteria WHERE criteria_Id=$id";
if ($conn->query($query) === TRUE) {
    echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . $conn->error;
}
$conn->close();
?>