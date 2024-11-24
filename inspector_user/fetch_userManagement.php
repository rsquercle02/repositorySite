<?php
require 'dbConnection.php';

$query = "SELECT * FROM userstable";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<tr class='align-middle'>
            <td>{$row['id']}</td>
            <td>{$row['fullname']}</td>
            <td>{$row['username']}</td>
            <td>{$row['password']}</td>
            <td>{$row['profile']}</td>
            <td>{$row['picture']}</td>
            <td>
                <button class='btn edit-btn btn-primary h-25' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$row['id']}' data-fullname='{$row['fullname']}' data-username='{$row['username']}' data-password='{$row['password']}' data-profile='{$row['profile']}' data-picture='{$row['picture']}'>Edit</button>
                <button class='btn delete-btn btn-primary h-25' data-id='{$row['id']}'>Delete</button>
            </td>
          </tr>";
}
$conn->close();
?>
