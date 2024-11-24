<?php
require 'dbConnection.php';

$searchMarket = $_GET['searchMarket'];
$query = "SELECT * FROM marketlist WHERE market_Name LIKE '%$searchMarket%'";
$markets = $conn->query($query);

if (!empty($markets)) {
    foreach ($markets as $market) {
        echo "<tr class='align-middle'>
                <td>{$market['market_Id']}</td>
                <td>{$market['market_Name']}</td>
                <td>{$market['market_Location']}</td>
                <td>{$market['market_Category']}</td>
                <td>
                    <button class='btn edit-btn btn-primary h-25' data-bs-toggle='modal' data-bs-target='#scheduleModal' data-marketid='{$market['market_Id']}' data-marketname='{$market['market_Name']}' data-marketlocation='{$market['market_Location']}' data-marketcategory='{$market['market_Category']}'>Add schedule</button>
                </td>
              </tr>";
    }
} else {
    echo '<p>No markets found.</p>';
}
?>
