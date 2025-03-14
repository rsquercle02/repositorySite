<?php
session_start();

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Stream;
// Database connection
require_once __DIR__ . '/Database.php';
$db = (new Database())->getConnection();

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../data');
$dotenv->load();

/** @var App $app */

$group->post('/register', function (Request $request, Response $response) use ($db) {
    try {
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        $file1 = $files['file1'];
        $file2 = $files['file2'];
        $file3 = $files['file3'];
        $file4 = $files['file4'];

        // Start the transaction
        $db->beginTransaction();

        // Define the SQL query for inserting into the `businessinformation` table
        $businfoquery = "INSERT INTO businessinformation (businessName, businessDescription, businessEmail, businessPhone, fromDay, toDay, operationDays, fromTime, toTime, operationTimes, region, province, municipality, barangay, streetBuildingHouse, businessType) 
                                          VALUES (:businessName, :businessDescription, :businessEmail, :businessPhone, :fromDay, :toDay, :operationDays, :fromTime, :toTime, :operationTimes, :region, :province, :municipality, :barangay, :streetBuildingHouse, :businessType)";
                                          
        $businfostmt = $db->prepare($businfoquery);
        // Bind parameters securely
        $businfostmt->bindParam(':businessName', $input['name']);
        $businfostmt->bindParam(':businessDescription', $input['description']);
        $businfostmt->bindParam(':businessEmail', $input['email']);
        $businfostmt->bindParam(':businessPhone', $input['busPhone']);
        $businfostmt->bindParam(':fromDay', $input['fromDay']);
        $businfostmt->bindParam(':toDay', $input['toDay']);
        $businfostmt->bindParam(':operationDays', $input['oprtDay']);
        $businfostmt->bindParam(':fromTime', $input['fromTime']);
        $businfostmt->bindParam(':toTime', $input['toTime']);
        $businfostmt->bindParam(':operationTimes', $input['oprtTime']);
        $businfostmt->bindParam(':region', $input['regionSelect']);
        $businfostmt->bindParam(':province', $input['provinceSelect']);
        $businfostmt->bindParam(':municipality', $input['municipalitySelect']);
        $businfostmt->bindParam(':barangay', $input['barangaySelect']);
        $businfostmt->bindParam(':streetBuildingHouse', $input['sbhNo']);
        $businfostmt->bindParam(':businessType', $input['businessType']);

        // Execute the query to insert the business info into the `businessinformation` table
        $businfostmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $businessId = $db->lastInsertId();

        // Define the SQL query for inserting into the `businessrepresentative` table
        $representativequery = "INSERT INTO businessrepresentative (firstName, middleName, lastName, suffix, age, role, ownerEmail, ownerPhone) 
                                          VALUES (:firstName, :middleName, :lastName, :suffix, :age, :role, :ownerEmail, :ownerPhone)";
                         
        $representativestmt = $db->prepare($representativequery);

        $representativestmt->bindParam(':firstName', $input['firstName']);
        $representativestmt->bindParam(':middleName', $input['middleName']);
        $representativestmt->bindParam(':lastName', $input['lastName']);
        $representativestmt->bindParam(':suffix', $input['suffix']);
        $representativestmt->bindParam(':age', $input['age']);
        $representativestmt->bindParam(':role', $input['role']);
        $representativestmt->bindParam(':ownerEmail', $input['ownrEmail']);
        $representativestmt->bindParam(':ownerPhone', $input['ownrPhone']);

        // Execute the query to insert the business info into the `businessrepresentative` table
        $representativestmt->execute();

        // Get parsed body data from the form
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        $file1 = $files['file1'];
        $file2 = $files['file2'];
        $file3 = $files['file3'];
        $file4 = $files['file4'];

        // Set the root upload directory
        $rootUploadDir = '../../uploads/';

        // Ensure the upload directory exists or create it
        if (!is_dir($rootUploadDir)) {
            mkdir($rootUploadDir, 0777, true); // Create the directory with full permissions if it doesn't exist
        }

        // Get the name value from the form
        $name = isset($input['name']) ? $input['name'] : '';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Create the root upload directory based on the name
        $uploadDir = $rootUploadDir . $name . '/';

        // Ensure the name-based directory exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions
        }

        // Allowed file types
        $allowedFileTypes = array(
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'image/jpeg', // .jpeg
            'image/png', // .png
            'application/pdf', // .pdf
        );

        // Function to handle file upload and return the file path
        function handleFileUpload($file, $uploadDir, $label, $allowedFileTypes)
        {
            
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

                // Create label directory if it doesn't exist
                $labelDir = $uploadDir . $label . '/';
                if (!is_dir($labelDir)) {
                    if (!mkdir($labelDir, 0777, true)) {
                        error_log("Failed to create directory: $labelDir");
                        return "Failed to create directory for file $fileName.";
                    }
                }

                // Define destination path
                $newFileName = uniqid() . "_" . basename($fileName);
                $destination = $labelDir . $newFileName;

                // Move the file
                $file->moveTo($destination);
                /*if (!$file->moveTo($destination)) {
                    error_log("Failed to move file $fileName to $destination");
                }*/

                return $destination;
            }
        }

        // Upload files and collect their paths
        $filePaths = [];
        $filePaths['file1'] = handleFileUpload($file1, $uploadDir, 'barangayclearance', $allowedFileTypes);
        $filePaths['file2'] = handleFileUpload($file2, $uploadDir, 'businesspermit', $allowedFileTypes);
        $filePaths['file3'] = handleFileUpload($file3, $uploadDir, 'occupancycertificate', $allowedFileTypes);
        $filePaths['file4'] = handleFileUpload($file4, $uploadDir, 'taxcertificate', $allowedFileTypes);

        // Check if any file failed to upload
        if (in_array(null, $filePaths) || in_array(true, array_map(function ($v) {
            return !is_string($v); // If any file path is not a string, that means there was an error
        }, $filePaths))) {
            return $response->withJson(['error' => 'Some files failed to upload. Please try again.'], 400);
        }else{
        // All files were uploaded successfully, now insert them into the database (you can do this here)
        // Define the SQL query for inserting into the `compliancecertificates` table
        $certificatequery = "INSERT INTO compliancecertificates (businessId, barangayClearance, businessPermit, occupancyCertificate, taxCertificate) 
                                          VALUES (:businessId, :barangayClearance, :businessPermit, :occupancyCertificate, :taxCertificate)";

        $certificatestmt = $db->prepare($certificatequery);

        $certificatestmt->bindParam(':businessId', $businessId);
        $certificatestmt->bindParam(':barangayClearance', $filePaths['file1']);
        $certificatestmt->bindParam(':businessPermit', $filePaths['file2']);
        $certificatestmt->bindParam(':occupancyCertificate', $filePaths['file3']);
        $certificatestmt->bindParam(':taxCertificate', $filePaths['file4']);
        
        // Execute the query to insert the business info into the `compliancecertificate` table
        $certificatestmt->execute();
        }

        // Define the SQL query for inserting into the `businessstatus` table
        $statusquery = "INSERT INTO businessstatus (businessId, businessStatus, statusReason) 
                                          VALUES (:businessId, :businessStatus, :statusReason)";

        $statusstmt = $db->prepare($statusquery);

        $businessStatus = "For verification";
        $statusReason = "The submitted documents are in verification process.";

        $statusstmt->bindParam(':businessId', $businessId);
        $statusstmt->bindParam(':businessStatus', $businessStatus);
        $statusstmt->bindParam(':statusReason', $statusReason);
        
        // Execute the query to insert the business info into the `compliancecertificate` table
        $statusstmt->execute();

    // Commit the transaction to make the changes permanent
    $db->commit();

    //echo "Data inserted successfully!";

    } catch (PDOException $e) {
         //If there’s an error, roll back the transaction
        $db->rollBack();
        
        echo "Error: " . $e->getMessage();
        
    }

    $response->getBody()->write(json_encode(['id' => $db->lastInsertId()]));
    return $response->withHeader('Content-Type', 'application/json');
});

