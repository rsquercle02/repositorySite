<?php
require 'dbConnection.php';

$id = $_POST['editId'];
$fullname = $_POST['editFullname'];
$username = $_POST['editUsername'];
$password = $_POST['editPassword'];
$profile = $_POST['editProfile'];
$picture = $_FILES["editPicture"]["tmp_name"];

if(empty($password) && empty($picture)){
$query = "UPDATE userstable SET fullname='$fullname', username='$username', profile='$profile' WHERE id=$id";
if ($conn->query($query) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}
$conn->close();
}else if(empty($password)){

    $query = "SELECT picture FROM userstable WHERE id=$id";
    $result = $conn->query($query);
    $link = $result->fetch_assoc();
    
    $filePath = $link['picture'];
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo "File deleted successfully.";
        } else {
            echo "Failed to delete file.";
        }
    } else {
        echo "File not found.";
    }
    
    
    $folder = "users/".$_POST["editUsername"];
    
    mkdir($folder, 0755);
    
    list($width, $height) = getimagesize($_FILES["editPicture"]["tmp_name"]);
    
    $srcImage = imagecreatefromjpeg($_FILES["editPicture"]["tmp_name"]);
    
    $newHeight = "500";
    
    $newWidth = "500";
    
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    $randomNumber = mt_rand(100,999);
    
    $photo = "users/".$_POST["editUsername"]."/".$randomNumber.".jpg";
    
    imagejpeg($destination, $photo);

    $query = "UPDATE userstable SET fullname='$fullname', username='$username', profile='$profile', picture='$photo' WHERE id=$id";
    if ($conn->query($query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $conn->close();
}else if(empty($picture)){

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "UPDATE userstable SET fullname='$fullname', username='$username', profile='$profile', password='$hashedPassword' WHERE id=$id";
    if ($conn->query($query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $conn->close();
}else{

    $query = "SELECT picture FROM userstable WHERE id=$id";
    $result = $conn->query($query);
    $link = $result->fetch_assoc();
    
    $filePath = $link['picture'];
    
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo "File deleted successfully.";
        } else {
            echo "Failed to delete file.";
        }
    } else {
        echo "File not found.";
    }
    
    
    $folder = "users/".$_POST["editUsername"];
    
    mkdir($folder, 0755);
    
    list($width, $height) = getimagesize($_FILES["editPicture"]["tmp_name"]);
    
    $srcImage = imagecreatefromjpeg($_FILES["editPicture"]["tmp_name"]);
    
    $newHeight = "500";
    
    $newWidth = "500";
    
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    $randomNumber = mt_rand(100,999);
    
    $photo = "users/".$_POST["editUsername"]."/".$randomNumber.".jpg";
    
    imagejpeg($destination, $photo);

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "UPDATE userstable SET fullname='$fullname', username='$username', profile='$profile', picture='$photo',  password='$hashedPassword' WHERE id=$id";
    if ($conn->query($query) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $conn->close();
}
?>
