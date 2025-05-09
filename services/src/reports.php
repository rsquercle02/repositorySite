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

$group->get('/allReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/lowriskReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/mediumriskReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'Medium risk.' AND rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'Medium risk.' AND rs. report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/highriskReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/resolvedReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT DISTINCT r.report_id, ar.action_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.update_at, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        JOIN actionreports ar ON r.report_id = ar.report_id
        WHERE rs.report_status ='Report resolved.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, ar.action_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.update_at, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        JOIN actionreports ar ON r.report_id = ar.report_id
        WHERE rs.report_status ='Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/ssearch/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT r.report_id, rs.report_status, rs.create_at, s.store_name, s.store_address
    FROM stores s
    JOIN
    reports r
    ON
    s.store_id = r.store_id
    JOIN
    rstatus rs
    ON
    r.report_id = rs.report_id
    WHERE
    rs.concerned_staff = 'Captain.' AND s.store_name LIKE :searchTerm
    ORDER BY rs.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/actionreportpost', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];

        if($input['statusSelect'] == "Report resolved."){
        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->bindValue(':actions', $input['reportText']);
        $stmt->bindValue(':staff', $input['staffSelect']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $actionId = $db->lastInsertId();

        // Get the name value
        $name = $input['staffSelect'];

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Get the current date in the format YYYYMMDD
        $currentDate = date('Ymd'); // For example, 20250205

        // Define the base upload directory
        $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

        // Define the action report ID (you may want to get this dynamically or from the form input)
        $actionReportId = $actionId;

        // Define the final upload directory, using the structure provided
        $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

        // Ensure the directory for the report exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Function to handle file upload and return the file path
        function actionFileUpload($file, $uploadDir, $actionId)
        {
            // Allowed file types
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
                $newFileName = $actionId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

                // Define the destination path (file will be saved in 'uploads/actionreports/kagawad/20250205_actionreportid/username_1234.jpg')
                $destination = $uploadDir . $newFileName;

                // Move the file to the destination folder
                $file->moveTo($destination);

                // Return the relative path (for example, 'uploads/actionreports/kagawad/20250205_actionreportid/username_1234.jpg')
                // Remove the leading '../../' from the path (if needed, depending on the context)
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
        $filePaths['file1'] = actionFileUpload($image1, $uploadDir, $actionId);

        $query = "INSERT INTO areportfiles (action_id, afile1) VALUES (:action_id, :afile1)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':action_id', $actionId);
        $stmt->bindValue(':afile1', $filePaths['file1']);
        $stmt->execute();

        $query = "UPDATE storeviolations SET store_violations = :store_violations WHERE store_id = :store_id";
        $stmt = $db->prepare($query);
        $violation = "None";
        $stmt->bindValue(':store_violations', $violation);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->execute();

        $query = "UPDATE rstatus SET report_status = :report_status WHERE report_id = :report_id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_status', $input['statusSelect']);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->execute();

        $statusquery = "UPDATE cstatus SET concern_status = :concern_status WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Resolved.";
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->execute();

        } else {

            // Start the transaction
            $db->beginTransaction();

            $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':report_id', $input['reportId']);
            $stmt->bindValue(':actions', $input['reportText']);
            $stmt->bindValue(':staff', $input['staffSelect']);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $actionId = $db->lastInsertId();

            // Get the name value
            $name = $input['staffSelect'];

            // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
            $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

            // Get the current date in the format YYYYMMDD
            $currentDate = date('Ymd'); // For example, 20250205

            // Define the base upload directory
            $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

            // Define the action report ID (you may want to get this dynamically or from the form input)
            $actionReportId = $actionId;

            // Define the final upload directory, using the structure provided
            $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

            // Ensure the directory for the report exists or create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
            }

            // Function to handle file upload and return the file path
            function handleFileUpload($file, $uploadDir, $actionId)
            {
                // Allowed file types
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
                    $newFileName = $actionId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

                    // Define the destination path (file will be saved in 'uploads/actionreports/kagawad/20250205_actionreportid/username_1234.jpg')
                    $destination = $uploadDir . $newFileName;

                    // Move the file to the destination folder
                    $file->moveTo($destination);

                    // Return the relative path (for example, 'uploads/actionreports/kagawad/20250205_actionreportid/username_1234.jpg')
                    // Remove the leading '../../' from the path (if needed, depending on the context)
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
            $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $actionId);

            $query = "INSERT INTO areportfiles (action_id, afile1) VALUES (:action_id, :afile1)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':action_id', $actionId);
            $stmt->bindValue(':afile1', $filePaths['file1']);
            $stmt->execute();

            $query = "UPDATE rstatus SET report_status = :report_status WHERE report_id = :report_id";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':report_status', $input['statusSelect']);
            $stmt->bindValue('report_id', $input['reportId']);
            $stmt->execute();
        }

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

$group->get('/reportdetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT 
    s.store_name,
    s.store_address,
    r.report_details, 
    cu.file1,
    rs.create_at
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN 
        concernedcitizen cc ON cc.concern_id = r.concern_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    WHERE r.report_id = :id
    ORDER BY 
        rs.create_at ASC;
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/************** Fetch report details********/
$group->get('/allfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    rs.report_status,
    rs.create_at, 
    s.store_id,
    s.store_name, 
    s.store_address,
    s.record_status,
    v.store_violations
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN 
        concernedcitizen cc ON cc.concern_id = r.concern_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    WHERE
        r.report_id = :id
    ORDER BY 
        rs.create_at ASC;
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/lowriskfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    rs.report_status,
    rs.create_at, 
    s.store_id,
    s.store_name, 
    s.store_address,
    s.record_status,
    v.store_violations
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN 
        concernedcitizen cc ON cc.concern_id = r.concern_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    WHERE 
        r.report_category = 'Low risk.' 
        AND r.report_id = :id
    ORDER BY 
        rs.create_at ASC;
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/mediumriskfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    rs.report_status,
    rs.create_at, 
    s.store_id,
    s.store_name, 
    s.store_address,
    s.record_status,
    v.store_violations
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN 
        concernedcitizen cc ON cc.concern_id = r.concern_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    WHERE 
        r.report_category = 'Medium risk.' 
        AND r.report_id = :id
    ORDER BY 
        rs.create_at ASC;
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/highriskfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT DISTINCT
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    rs.report_status,
    rs.create_at, 
    s.store_id,
    s.store_name, 
    s.store_address,
    s.record_status,
    v.store_violations
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN 
        concernedcitizen cc ON cc.concern_id = r.concern_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    WHERE 
        r.report_category = 'High risk.' 
        AND r.report_id = :id
    ORDER BY 
        rs.create_at ASC;";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/resolvedfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff, af.afile1
        FROM stores s
        JOIN reports r ON s.store_id = r.store_id
        JOIN rstatus rs ON r.report_id = rs.report_id
        JOIN actionreports ar ON rs.report_id = ar.report_id
        JOIN areportfiles af ON ar.action_id = af.action_id
        WHERE rs.report_status = 'Report resolved.' AND ar.action_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/***************** Fetch all reports ****************/
$group->get('/Reports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/createdlowrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/createdmediumrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'Medium risk.' AND rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'Medium risk.' AND rs. report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/createdhighrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report created.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/createdReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status ='Report created.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, rs.report_status, s.store_name, s.store_address, r.report_details
            FROM reports r 
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN stores s ON r.store_id = s.store_id
            WHERE rs.report_status ='Report created.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/************* Fetch resolved reports ********/
$group->get('/allresolvedReports', function (Request $request, Response $response) use ($db) {
    $query = "SELECT r.report_id, rs.report_status, rs.create_at, s.store_name, s.store_address
    FROM stores s
    JOIN
    reports r
    ON
    s.store_id = r.store_id
    JOIN
    rstatus rs
    ON
    r.report_id = rs.report_id
    WHERE rs.report_status = 'Report resolved.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/resolvedlowrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report resolved.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/resolvedmediumrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'Medium risk.' AND rs.report_status = 'Report resolved.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'Medium risk.' AND rs.report_status = 'Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/resolvedhighrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report resolved.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.create_at, r.report_category, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/************** Action reports **************/
$group->get('/actionlowrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff
            FROM stores s
            JOIN reports r ON s.store_id = r.store_id
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN actionreports ar ON rs.report_id = ar.report_id
            WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report resolved.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff
            FROM stores s
            JOIN reports r ON s.store_id = r.store_id
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN actionreports ar ON rs.report_id = ar.report_id
            WHERE r.report_category = 'Low risk.' AND rs.report_status = 'Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/actionmediumrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff
            FROM stores s
            JOIN reports r ON s.store_id = r.store_id
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN actionreports ar ON rs.report_id = ar.report_id
            WHERE r.report_category = 'Medium risk.' AND rs.report_status = 'Report resolved.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff
            FROM stores s
            JOIN reports r ON s.store_id = r.store_id
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN actionreports ar ON rs.report_id = ar.report_id
            WHERE r.report_category = 'Medium risk.' AND rs.report_status = 'Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/actionhighrisk', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT DISTINCT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff
            FROM stores s
            JOIN reports r ON s.store_id = r.store_id
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN actionreports ar ON rs.report_id = ar.report_id
            WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report resolved.' AND s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT DISTINCT s.store_name, s.store_address, ar.action_id, ar.report_id, ar.create_at, ar.actions, ar.staff
            FROM stores s
            JOIN reports r ON s.store_id = r.store_id
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN actionreports ar ON rs.report_id = ar.report_id
            WHERE r.report_category = 'High risk.' AND rs.report_status = 'Report resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});