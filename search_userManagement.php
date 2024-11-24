<?php
require 'dbConnection.php';

$searchTerm = $_GET['searchTerm'];
$query = "SELECT * FROM userstable WHERE fullname LIKE '%$searchTerm%'";
$users = $conn->query($query);

if (!empty($users)) {
    foreach ($users as $user) {
        echo "<tr class='align-middle'>
                <td>{$user['id']}</td>
                <td>{$user['fullname']}</td>
                <td>{$user['username']}</td>
                <td>{$user['password']}</td>
                <td>{$user['profile']}</td>
                <td>{$user['picture']}</td>
                <td>
                    <button class='btn edit-btn btn-primary h-25' data-bs-toggle='modal' data-bs-target='#editModal' data-id='{$user['id']}' data-fullname='{$user['fullname']}' data-username='{$user['username']}' data-profile='{$user['profile']}' data-picture='{$user['picture']}'>Edit</button>
                    <button class='btn delete-btn btn-primary h-25' data-id='{$user['id']}'>Delete</button>
                </td>
              </tr>";
    }
} else {
    echo '<p>No users found.</p>';
}
?>
