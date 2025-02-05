<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\UploadedFile;
use Slim\Psr7\Stream;
// Database connection
require_once __DIR__ . '/Database.php';
$db = (new Database1())->getConnection();

/** @var App $app */

$group->get('/fetch', function (Request $request, Response $response) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    bi.businessDescription
    FROM
        businessinformation bi
    JOIN
        businessstatus bs
    ON
        bi.businessId = bs.businessId
    WHERE
        bs.businessstatus = 'For verification'
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetch/{businessId}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT businessId, businessName, streetBuildingHouse, barangay, municipality FROM businessinformation WHERE businessId = :businessId";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':businessId', $args['businessId'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/inspector/{assignedDay}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT inspectorName FROM `inspectorsinformation` WHERE assignedDay = :assignedDay";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':assignedDay', $args['assignedDay'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/schedule', function (Request $request, Response $response) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    bi.businessDescription
    FROM
        businessinformation bi
    JOIN
        businessstatus bs
    ON
        bi.businessId = bs.businessId
    WHERE
        bs.businessstatus = 'Verified, for schedule.'
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchDocument/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId, 
    bi.businessName, 
    bi.businessType, 
    cc.barangayClearance, 
    cc.businessPermit, 
    cc.occupancyCertificate,
    cc.taxCertificate
    FROM 
        businessinformation bi
    JOIN 
        compliancecertificates cc 
    ON 
        bi.businessId = cc.businessId
    WHERE
        bi.businessId = :id
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/search/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    bi.businessDescription
    FROM
        businessinformation bi
    JOIN
        businessstatus bs
    ON
        bi.businessId = bs.businessId
    WHERE
        bs.businessstatus = 'For verification'
    AND
        bi.businessName LIKE :searchTerm;";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/schedule/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    bi.businessDescription
    FROM
        businessinformation bi
    JOIN
        businessstatus bs
    ON
        bi.businessId = bs.businessId
    WHERE
        bs.businessstatus = 'Verified, for schedule.'
    AND
        bi.businessName LIKE :searchTerm;";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

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

$group->put('/status/{businessId}', function (Request $request, Response $response, $args) use ($db) {
    try {
        $input = $request->getParsedBody();
        $statusquery = "UPDATE businessstatus SET businessStatus = :businessStatus, statusReason = :statusReason WHERE businessId = :businessId";

        $statusstmt = $db->prepare($statusquery);

        $statusstmt->bindParam(':businessStatus', $input['businessStatus']);
        $statusstmt->bindParam(':statusReason', $input['statusReason']);
        $statusstmt->bindParam(':businessId', $args['businessId'], PDO::PARAM_INT);
        
        // Execute the query to insert the business info into the `compliancecertificate` table
        $statusstmt->execute();

        $businessStatus = $input['businessStatus'];
        $statusReason = $input['statusReason'];
        $businessId = $args['businessId'];
        error_log("hello world t");
        error_log("$businessStatus $statusReason $businessId");

    } catch (PDOException $e) {
        //If there’s an error, roll back the transaction
       //$db->rollBack();
       error_log("hello world c");
       echo "Error: " . $e->getMessage();
       
   }

   $response->getBody()->write(json_encode(['id' => $db->lastInsertId()]));
   return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/registerfile', function (Request $request, Response $response) use ($db) {


// Get parsed body data from the form
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
    /* function handleFile($file, $uploadDir, $label, $allowedFileTypes)
    {
        if ($file) {
            // Get the file details from the Slim\Psr7\UploadedFile object
            $fileName = $file->getClientFilename();
            $fileSize = $file->getSize();
            $fileType = $file->getClientMediaType();
            $fileError = $file->getError();

            // Check if the file is valid
            if ($fileError === UPLOAD_ERR_OK) {
                // Validate file type
                if (in_array($fileType, $allowedFileTypes)) {
                    // Create a unique filename to avoid overwriting
                    $newFileName = uniqid() . "_" . basename($fileName);

                    // Create the directory for the label inside the name-based directory
                    $labelDir = $uploadDir . $label . '/';
                    if (!is_dir($labelDir)) {
                        mkdir($labelDir, 0777, true); // Create the directory for the file's label
                    }

                    // Define the final destination path for the file
                    $destination = $labelDir . $newFileName;

                    // Move the file to the desired folder
                    if ($file->moveTo($destination)) {
                        error_log("File $fileName moved successfully to $destination");
                    } else {
                        error_log("Failed to move file $fileName to $destination");
                    }

                    return $destination;
                } else {
                    return "Invalid file type for file $fileName. Only .doc, .docx, .jpeg, .png, and .pdf are allowed.";
                }
            } else {
                echo "<script>console.log('Error uploading file $fileName. Error code: $fileError.');</script>";


            }
        }
        return null;
    }*/

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

    $file1 = $filePaths['file1'];
    $file2 = $filePaths['file2'];
    $file3= $filePaths['file3'];
    $file4 = $filePaths['file4'];

            error_log("The file path $file1 $file2 $file3 $file4.");
    // Check if any file failed to upload
    if (in_array(null, $filePaths) || in_array(true, array_map(function ($v) {
        return !is_string($v); // If any file path is not a string, that means there was an error
    }, $filePaths))) {
        return $response->withJson(['error' => 'Some files failed to upload. Please try again.'], 400);
    }

    // All files were uploaded successfully, now insert them into the database (you can do this here)

$response->getBody()->write(json_encode(['id' => $db->lastInsertId()]));
return $response->withHeader('Content-Type', 'application/json');

});

$group->put('/object/{id}', function (Request $request, Response $response, $args) use ($db) {
    $input = $request->getParsedBody();
    $query = "UPDATE items SET name = :name, description = :description WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':description', $input['description']);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $response->getBody()->write(json_encode(['status' => 'success']));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->delete('/object/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "DELETE FROM items WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $response->getBody()->write(json_encode(['status' => 'success']));
    return $response->withHeader('Content-Type', 'application/json');
});
