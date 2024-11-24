<?php
require 'dbConnection.php';

$query = "SELECT * FROM inspectionschedule";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<tr class='align-middle'>
            <td>{$row['schedule_Id']}</td>
            <td>{$row['market_Name']}</td>
            <td>{$row['inspection_date']}</td>
            <td>{$row['inspection_time']}</td>
            <td>
                <button class='btn edit-btn btn-primary h-25' data-bs-toggle='modal' data-bs-target='#inspectionModal' data-scheduleid='{$row['schedule_Id']}' data-marketid='{$row['market_Id']}' data-marketname='{$row['market_Name']}' data-marketlocation='{$row['market_Location']}' data-marketcategory='{$row['market_Category']}' data-inspectiondate='{$row['inspection_date']}' data-inspectiontime='{$row['inspection_time']}'></button>
            </td>
          </tr>";
}
$conn->close();
?>
