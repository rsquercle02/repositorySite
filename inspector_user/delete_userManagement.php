<?php
require 'dbConnection.php';

$id = $_POST['id'];

$query = "SELECT username FROM userstable WHERE id = $id";
$result = $conn->query($query);
$link = $result->fetch_assoc();

$folderPath = "users/".$link['username'];

function deleteFolder($folderPath) {
    if (!is_dir($folderPath)) {
        return false; // The specified path is not a directory
    }

    $files = array_diff(scandir($folderPath), array('.', '..'));

    foreach ($files as $file) {
        $filePath = $folderPath . '/' . $file;
        if (is_dir($filePath)) {
            deleteFolder($filePath); // Recursively delete subfolders
        } else {
            unlink($filePath); // Delete files
        }
    }

    return rmdir($folderPath); // Delete the main folder
}

// Example usage:
if (deleteFolder($folderPath)) {
    echo "Folder deleted successfully.";
} else {
    echo "Failed to delete folder.";
}


$query = "DELETE FROM userstable WHERE id=$id";
if ($conn->query($query) === TRUE) {
    echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . $conn->error;
}
$conn->close();
?>
