<?php
require 'dbConnection.php';

$query = "SELECT * FROM inspectioncriteria";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<tr class='align-middle'>
            <td>{$row['criteria_Id']}</td>
            <td>{$row['category']}</td>
            <td>{$row['question']}</td>
            <td>
                <button class='btn delete-btn btn-primary h-25' data-criteria_id='{$row['criteria_Id']}'>Delete</button>
            </td>
          </tr>";
}
$conn->close();
?>
