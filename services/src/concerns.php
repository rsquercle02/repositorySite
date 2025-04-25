<?php
//session_start();
require __DIR__ . '/../vendor/autoload.php';  // Include PHPMailer via Composer

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Stream;
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
require_once __DIR__ . '/Database.php';
$db = (new Database())->getConnection();

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../data');
$dotenv->load();

/** @var App $app */

$group->get('/fetchStore', function (Request $request, Response $response) use ($db) {
    // Retrieve the 'term' query parameter from the URL
    $queryparams = $request->getQueryParams('term');
    $searchTerm = $queryparams['term'];

    $query = "SELECT * FROM stores WHERE StoreName LIKE :searchTerm";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/postconcerns', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];
        $image2 = $files['image2'];
        $image3 = $files['image3'];
    
        // Get the name value from the form
        $name = isset($input['storeName']) ? $input['storeName'] : '';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Get the current date in the format YYYYMMDD
        $currentDate = date('Ymd'); // For example, 20250205

        // Define the upload directory (relative path) including the date and name
        $uploadDir = '../../uploads/' . $name . '/' . $currentDate . $name . '/';

        // Ensure the directory for the store exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Function to handle file upload and return the file path 
        function handleFileUploads($file, $uploadDir, $username){
            // Allowed file types (moved inside the function)
            $allowedFileTypes = array(
                'application/msword', // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'image/jpeg', // .jpeg
                'image/png', // .png
                'application/pdf', // .pdf
            );

            if ($file) {
                // Get file details
                $fileName = $file->getClientFilename();
                $fileSize = $file->getSize();
                $fileType = $file->getClientMediaType();
                $fileError = $file->getError();

                // Log file error (if any)
                if ($fileError !== UPLOAD_ERR_OK) {
                    error_log("Error uploading file $fileName. Error code: $fileError");
                    return "Error uploading file $fileName. Error code: $fileError.";
                }

                // Validate file type
                if (!in_array($fileType, $allowedFileTypes)) {
                    error_log("Invalid file type for file $fileName. Only .jpeg, .png, and .pdf are allowed.");
                    return "Invalid file type for file $fileName. Only .jpeg, .png, and .pdf are allowed.";
                }

                // Generate a new filename using the username and a random number
                $randomNumber = mt_rand(1000, 9999); // Generate a random number between 1000 and 9999
                $newFileName = $username . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

                // Define the destination path (file will be saved in 'uploads/storeName/yyyyMMddname/username_1234.jpg')
                $destination = $uploadDir . $newFileName;

                // Move the file to the destination folder
                $file->moveTo($destination);

                // Return the relative path (for example, 'uploads/john_doe/20250205john_doe/username_1234.jpg')
                // Remove the leading '../../' from the path
                if (substr($destination, 0, 6) === "../../") {
                    $destination = substr($destination, 6); // Remove the first 6 characters
                }
                return $destination;
            }

            // Return error if file is not uploaded properly
            return "No file uploaded.";
        }



        // Upload files and collect their paths
        $filePaths = [];
        $filePaths['file1'] = handleFileUploads($image1, $uploadDir, $input['storeName']);
        $filePaths['file2'] = handleFileUploads($image2, $uploadDir, $input['storeName']);
        $filePaths['file3'] = handleFileUploads($image3, $uploadDir, $input['storeName']);

        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO stores (store_name, store_address, record_status) VALUES (:store_name, :store_address, :record_status)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_name', $input['storeName']);
        $stmt->bindValue(':store_address', $input['storeAddress']);
        $stmt->bindValue(':record_status', $input['recordStatus']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $storeId = $db->lastInsertId();

        $query = "INSERT INTO concerns (store_id, concern_details) VALUES (:store_id, :concern_details)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_id', $storeId);
        $stmt->bindValue(':concern_details', $input['concerns']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $concernId = $db->lastInsertId();

        $query = "INSERT INTO concernedcitizen (concern_id, firstname, middlename, lastname, anonymity_status) VALUES (:concern_id, :firstname, :middlename, :lastname, :anonymity_status)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':concern_id', $concernId);
        $stmt->bindValue(':firstname', $input['firstname']);
        $stmt->bindValue(':middlename', $input['middlename']);
        $stmt->bindValue(':lastname', $input['lastname']);
        $stmt->bindValue(':anonymity_status', $input['anonymityStatus']);
        $stmt->execute();

        //files query
        $query = "INSERT INTO concernsuploads (concern_id, file1, file2, file3) VALUES (:concern_id, :file1, :file2, :file3)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':concern_id', $concernId);
        $stmt->bindParam(':file1', $filePaths['file1']);
        $stmt->bindParam(':file2', $filePaths['file2']);
        $stmt->bindParam(':file3', $filePaths['file3']);
        $stmt->execute();

        //concern status
        $statusquery = "INSERT INTO cstatus (concern_id, concern_status, cstatus_reason) VALUES (:concern_id, :concern_status, :cstatus_reason)";
        $statusstmt = $db->prepare($statusquery);
        $concernStatus = "No action.";
        $cstatusReason = "The concern are posted to barangay.";
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
        $statusstmt->execute();

        // Commit the transaction to make the changes permanent
        $db->commit();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'Concern uploaded successfully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        // If there's a database error (PDOException), handle it here
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    } catch (Exception $e) {
        // Catch any other general exceptions
        $response->getBody()->write(json_encode(['error' => 'An error occurred: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Internal Server Error
    }
});