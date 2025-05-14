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

$group->post('/postconcerns', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];
    
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

        // Start the transaction
        $db->beginTransaction();

        $storeId = $input['storeId']; 

        if ($storeId == 'null') {
            // Check if store_name already exists
        $query = "SELECT store_id FROM nonregisteredstores WHERE store_name LIKE :store_name AND store_address LIKE :store_address";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_name', '%' . $input['storeName'] . '%');
        $stmt->bindValue(':store_address', '%' . $input['storeAddress'] . '%');
        $stmt->execute();
        $existingStoreId = $stmt->fetchColumn();

        if ($existingStoreId) {
            // If store_name exists, use existing store_id
            $newCode = $existingStoreId;
        } else {
            // Generate new unique store_code
            $query = "SELECT store_id FROM nonregisteredstores ORDER BY store_id DESC LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $lastCode = $stmt->fetchColumn();

            if ($lastCode) {
                $num = (int)substr($lastCode, 2) + 1;
                $newCode = 'NR' . str_pad($num, 3, '0', STR_PAD_LEFT);
            } else {
                $newCode = 'NR001';
            }

            // Insert into nonregisteredstores
            $query = "INSERT INTO nonregisteredstores (store_id, store_name, store_address, record_status) 
                    VALUES (:store_id, :store_name, :store_address, :record_status)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':store_id', $newCode);
            $stmt->bindValue(':store_name', $input['storeName']);
            $stmt->bindValue(':store_address', $input['storeAddress']);
            $stmt->bindValue(':record_status', $input['recordStatus']);
            $stmt->execute();
        }

            $storeId = $newCode; 

        } else {
            // Use provided storeId
            $storeId = $storeId;
        }

        // Insert into stores (using numeric storeId — not store_code)
        $query = "INSERT INTO stores (store_id, store_name, store_address, record_status) 
                VALUES (:store_id, :store_name, :store_address, :record_status)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_id', $storeId);
        $stmt->bindValue(':store_name', $input['storeName']);
        $stmt->bindValue(':store_address', $input['storeAddress']);
        $stmt->bindValue(':record_status', $input['recordStatus']);
        $stmt->execute();

        $query = "INSERT INTO concerns (store_id, concern_details) VALUES (:store_id, :concern_details)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_id', $storeId);
        $stmt->bindValue(':concern_details', $input['concerns']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $concernId = $db->lastInsertId();

        $query = "INSERT INTO concernedcitizen (concern_id, account_id, firstname, middlename, lastname, resident_type, anonymity_status) VALUES (:concern_id, :account_id, :firstname, :middlename, :lastname, :resident_type, :anonymity_status)";
        $stmt = $db->prepare($query);
        // Check if the session variable 'id' is set and not empty
        if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
            $accountId = $_SESSION['id'];
        }
        $stmt->bindValue(':concern_id', $concernId);
        $stmt->bindValue(':account_id', $accountId);
        $stmt->bindValue(':firstname', $input['firstname']);
        $stmt->bindValue(':middlename', $input['middlename']);
        $stmt->bindValue(':lastname', $input['lastname']);
        $stmt->bindValue(':resident_type', $input['residentType']);
        $stmt->bindValue(':anonymity_status', $input['anonymityStatus']);
        $stmt->execute();

        //files query
        $query = "INSERT INTO concernsuploads (concern_id, file1) VALUES (:concern_id, :file1)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':concern_id', $concernId);
        $stmt->bindParam(':file1', $filePaths['file1']);
        $stmt->execute();

        //concern status
        $statusquery = "INSERT INTO cstatus (concern_id, concern_status) VALUES (:concern_id, :concern_status)";
        $statusstmt = $db->prepare($statusquery);
        $concernStatus = "No action.";
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->bindParam(':concern_status', $concernStatus);
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

$group->get('/fetchConcerns', function (Request $request, Response $response) use ($db) {
    $query = "SELECT DISTINCT
    c.concern_id,
    cc.resident_type,
    cc.anonymity_status,
    s.store_name,
    s.store_address,
    cst.concern_status,
    c.create_at
    FROM
        concerns c
    JOIN
        stores s
    ON
    	c.store_id = s.store_id
    JOIN
        concernedcitizen cc
    ON
    	cc.concern_id = c.concern_id
    JOIN
        cstatus cst
    ON
        c.concern_id = cst.concern_id
    WHERE
        cst.concern_status = 'No action.'
    ORDER BY c.create_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/concernsReport', function (Request $request, Response $response) use ($db) {
    $query = "SELECT DISTINCT
    c.concern_id,
    cc.resident_type,
    cc.anonymity_status,
    s.store_name,
    s.store_address,
    cst.concern_status,
    c.create_at
    FROM
        concerns c
    JOIN
        stores s
    ON
    	c.store_id = s.store_id
    JOIN
        concernedcitizen cc
    ON
    	cc.concern_id = c.concern_id
    JOIN
        cstatus cst
    ON
        c.concern_id = cst.concern_id
    WHERE
        cc.account_id = :account_id
    ORDER BY c.create_at DESC";
    $stmt = $db->prepare($query);
    // Check if the session variable 'id' is set and not empty
    if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
        $accountId = $_SESSION['id'];
    }
    $stmt->bindValue(':account_id', $accountId);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/postreport', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();

        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO reports (concern_id, store_id, report_details, report_category) VALUES (:concern_id, :store_id, :report_details, :report_category)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':concern_id', $input['concernId']);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->bindValue(':report_details', $input['reportText']);
        $stmt->bindValue(':report_category', $input['categorySelect']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        $query = "INSERT INTO storeviolations (store_id, store_violations) VALUES (:store_id, :store_violations)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->bindValue(':store_violations', $input['violationSelect']);
        $stmt->execute();

        $query = "INSERT INTO rstatus (report_id, report_status) VALUES (:report_id, :report_status)";
        $stmt = $db->prepare($query);
        $stmt->bindValue('report_id', $reportId);     
        $reportStatus = 'Report created.';
        $stmt->bindValue(':report_status', $reportStatus);
        $stmt->execute();

        //update status
        $statusquery = "UPDATE cstatus SET concern_status = :concern_status WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Reported.";
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->execute();

        // Commit the transaction to make the changes permanent
        $db->commit();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'Report created successfully.']));
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

$group->post('/notValid', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();

        // Start the transaction
        $db->beginTransaction();

        //update status
        $statusquery = "UPDATE cstatus SET concern_status = :concern_status WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $statusstmt->bindParam(':concern_id', $input['concernId']);
        $statusstmt->bindParam(':concern_status', $input['concernStatus']);
        $statusstmt->execute();

        // Commit the transaction to make the changes permanent
        $db->commit();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'Update successfully.']));
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

$group->get('/searchBusiness/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    c.concern_id,
    cc.resident_type,
    cc.anonymity_status,
    s.store_name,
    s.store_address,
    cst.concern_status,
    c.create_at
    FROM
        concerns c
    JOIN
        stores s
    ON
    	c.store_id = s.store_id
    JOIN
        concernedcitizen cc
    ON
    	cc.concern_id = c.concern_id
    JOIN
        cstatus cst
    ON
        c.concern_id = cst.concern_id
    WHERE
        cst.concern_status = 'No action.' AND s.store_name LIKE :searchTerm
    ORDER BY c.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/searchConcerns/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    c.concern_id,
    cc.resident_type,
    cc.anonymity_status,
    s.store_name,
    s.store_address,
    cst.concern_status,
    c.create_at
    FROM
        concerns c
    JOIN
        stores s
    ON
    	c.store_id = s.store_id
    JOIN
        concernedcitizen cc
    ON
    	cc.concern_id = c.concern_id
    JOIN
        cstatus cst
    ON
        c.concern_id = cst.concern_id
    WHERE
        cc.account_id = :account_id AND s.store_name LIKE :searchTerm
    ORDER BY c.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    // Check if the session variable 'id' is set and not empty
    if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
        $accountId = $_SESSION['id'];
    }
    $stmt->bindValue(':account_id', $accountId);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchDetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    c.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname,
    s.store_id,
    s.store_name,
    s.store_address,
    s.record_status,
    c.concern_details,
    cu.file1,
    cst.concern_status,
    c.create_at
    FROM
        stores s
    JOIN
        concerns c
    ON
    	s.store_id = c.store_id
    JOIN
        concernedcitizen cc
    ON
    	cc.concern_id = c.concern_id
    JOIN
        concernsuploads cu
    ON
    	c.concern_id = cu.concern_id
    JOIN
        cstatus cst
    ON
        c.concern_id = cst.concern_id
    WHERE
        cst.concern_status = 'No action.' AND c.concern_id = :id
    ORDER BY c.create_at ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/concernDetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    c.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname,
    s.store_id,
    s.store_name,
    s.store_address,
    s.record_status,
    c.concern_details,
    cu.file1,
    cst.concern_status,
    c.create_at
    FROM
        stores s
    JOIN
        concerns c
    ON
    	s.store_id = c.store_id
    JOIN
        concernedcitizen cc
    ON
    	cc.concern_id = c.concern_id
    JOIN
        concernsuploads cu
    ON
    	c.concern_id = cu.concern_id
    JOIN
        cstatus cst
    ON
        c.concern_id = cst.concern_id
    WHERE
        c.concern_id = :id
    ORDER BY c.create_at ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/************* Tracking Concerns ***********/
$group->get('/Concerns', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
	        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id 
            WHERE s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
	        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/NoactionConcerns', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
        WHERE cs.concern_status ='No action.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
	        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
            WHERE cs.concern_status ='No action.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/ReportedConcerns', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
        WHERE cs.concern_status ='Reported.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
	        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
            WHERE cs.concern_status ='Reported.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/ResolvedConcerns', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
        WHERE cs.concern_status ='Resolved.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
	        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
            WHERE cs.concern_status ='Resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/notValidConcerns', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
        WHERE cs.concern_status ='Not valid.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT DISTINCT c.concern_id, cc.resident_type, cc.anonymity_status, cs.concern_status, CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
	        JOIN concernedcitizen cc ON cc.concern_id = c.concern_id
            WHERE cs.concern_status ='Not valid.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/stores', function (Request $request, Response $response) use ($db) {
    $query = "SELECT * FROM nonregisteredstores";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});