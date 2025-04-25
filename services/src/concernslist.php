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

$group->get('/fetchConcerns', function (Request $request, Response $response) use ($db) {
    $query = "SELECT
    c.concern_id,
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
    ORDER BY c.create_at ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/searchBusiness/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    c.concern_id,
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
    ORDER BY c.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchDetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    c.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname,
    s.store_id,
    s.store_name,
    s.store_address,
    s.record_status,
    c.concern_details,
    cu.file1,
    cu.file2,
    cu.file3,
    cst.concern_status,
    cst.cstatus_reason,
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

$group->post('/postreport', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();

        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO reports (concern_id, store_id, report_details) VALUES (:concern_id, :store_id, :report_details)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':concern_id', $input['concernId']);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->bindValue(':report_details', $input['reportText']);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        $query = "INSERT INTO storeviolations (store_id, store_violations) VALUES (:store_id, :store_violations)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->bindValue(':store_violations', $input['violationSelect']);
        $stmt->execute();

        $query = "INSERT INTO rstatus (report_id, concerned_staff, report_status, rstatus_reason) VALUES (:report_id, :concerned_staff, :report_status, :rstatus_reason)";
        $stmt = $db->prepare($query);
        $stmt->bindValue('report_id', $reportId);
        $stmt->bindValue(':concerned_staff', $input['staffSelect']);
        $reportStatus = 'No action.';
        $stmt->bindValue(':report_status', $reportStatus);
        $rStatusReason = 'Forward to concerned staff.';
        $stmt->bindValue(':rstatus_reason', $rStatusReason);
        $stmt->execute();

        //update status
        $statusquery = "UPDATE cstatus SET concern_status = :concern_status, cstatus_reason = :cstatus_reason WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Reported.";
        $cstatusReason = "The report is created for the concern.";
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
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

/*************Kagawad 1*************/
$group->get('/k1fetchReports', function (Request $request, Response $response) use ($db) {
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
    rs.concerned_staff = 'Kagawad 1.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/k1search/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
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
    rs.concerned_staff = 'Kagawad 1.' AND s.store_name LIKE :searchTerm
    ORDER BY rs.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/k1fetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT 
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    cu.file2,
    cu.file3,
    rs.report_status,
    rs.rstatus_reason,  
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
        rs.concerned_staff = 'Kagawad 1.' 
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

$group->post('/k1post', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];
        $image2 = $files['image2'];
        $image3 = $files['image3'];

        if($input['statusSelect'] == "Resolved."){
        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->bindValue(':actions', $input['reportText']);
        $staff = 'Kagawad 1.';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Kagawad 1.';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Get the current date in the format YYYYMMDD
        $currentDate = date('Ymd'); // For example, 20250205

        // Define the base upload directory
        $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

        // Define the action report ID (you may want to get this dynamically or from the form input)
        $actionReportId = $reportId;

        // Define the final upload directory, using the structure provided
        $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

        // Ensure the directory for the report exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Function to handle file upload and return the file path
        function handleFileUpload($file, $uploadDir, $reportId)
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
                $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
        $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
        $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
        $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

        $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':action_id', $reportId);
        $stmt->bindValue(':afile1', $filePaths['file1']);
        $stmt->bindValue(':afile2', $filePaths['file2']);
        $stmt->bindValue(':afile3', $filePaths['file3']);
        $stmt->execute();

        $query = "UPDATE storeviolations SET store_violations = :store_violations WHERE store_id = :store_id";
        $stmt = $db->prepare($query);
        $violation = "None";
        $stmt->bindValue(':store_violations', $violation);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->execute();

        $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
        $stmt = $db->prepare($query);
        $concernedStaff = "None";
        $stmt->bindValue(':concerned_staff', $concernedStaff);
        $stmt->bindValue(':report_status', $input['statusSelect']);
        $rstatusReason = "The report is resolved.";
        $stmt->bindValue(':rstatus_reason', $rstatusReason);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->execute();

        $statusquery = "UPDATE cstatus SET concern_status = :concern_status, cstatus_reason = :cstatus_reason WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Resolved.";
        $cstatusReason = "The concern is resolved.";
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->execute();

        } else {

            // Start the transaction
            $db->beginTransaction();

            $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':report_id', $input['reportId']);
            $stmt->bindValue(':actions', $input['reportText']);
            $staff = 'Kagawad 1.';
            $stmt->bindValue(':staff', $staff);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $reportId = $db->lastInsertId();

            // Get the name value
            $name = 'Kagawad 1.';

            // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
            $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

            // Get the current date in the format YYYYMMDD
            $currentDate = date('Ymd'); // For example, 20250205

            // Define the base upload directory
            $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

            // Define the action report ID (you may want to get this dynamically or from the form input)
            $actionReportId = $reportId;

            // Define the final upload directory, using the structure provided
            $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

            // Ensure the directory for the report exists or create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
            }

            // Function to handle file upload and return the file path
            function handleFileUpload($file, $uploadDir, $reportId)
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
                    $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
            $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
            $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
            $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

            $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':action_id', $reportId);
            $stmt->bindValue(':afile1', $filePaths['file1']);
            $stmt->bindValue(':afile2', $filePaths['file2']);
            $stmt->bindValue(':afile3', $filePaths['file3']);
            $stmt->execute();

            $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
            $stmt = $db->prepare($query);
            $concernedStaff = 'Captain.';
            $stmt->bindValue(':concerned_staff', $concernedStaff);
            $stmt->bindValue(':report_status', $input['statusSelect']);
            $rstatusReason = "The report is not resolved.";
            $stmt->bindValue(':rstatus_reason', $rstatusReason);
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

/*************Kagawad 2*************/
$group->get('/k2fetchReports', function (Request $request, Response $response) use ($db) {
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
    rs.concerned_staff = 'Kagawad 2.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/k2search/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
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
    rs.concerned_staff = 'Kagawad 2.' AND s.store_name LIKE :searchTerm
    ORDER BY rs.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/k2fetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT 
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    cu.file2,
    cu.file3,
    rs.report_status,
    rs.rstatus_reason,  
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
        rs.concerned_staff = 'Kagawad 1.' 
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

$group->post('/k2post', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];
        $image2 = $files['image2'];
        $image3 = $files['image3'];

        if($input['statusSelect'] == "Resolved."){
        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->bindValue(':actions', $input['reportText']);
        $staff = 'Kagawad 2.';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Kagawad 2.';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Get the current date in the format YYYYMMDD
        $currentDate = date('Ymd'); // For example, 20250205

        // Define the base upload directory
        $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

        // Define the action report ID (you may want to get this dynamically or from the form input)
        $actionReportId = $reportId;

        // Define the final upload directory, using the structure provided
        $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

        // Ensure the directory for the report exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Function to handle file upload and return the file path
        function handleFileUpload($file, $uploadDir, $reportId)
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
                $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
        $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
        $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
        $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

        $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':action_id', $reportId);
        $stmt->bindValue(':afile1', $filePaths['file1']);
        $stmt->bindValue(':afile2', $filePaths['file2']);
        $stmt->bindValue(':afile3', $filePaths['file3']);
        $stmt->execute();

        $query = "UPDATE storeviolations SET store_violations = :store_violations WHERE store_id = :store_id";
        $stmt = $db->prepare($query);
        $violation = "None";
        $stmt->bindValue(':store_violations', $violation);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->execute();

        $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
        $stmt = $db->prepare($query);
        $concernedStaff = "None";
        $stmt->bindValue(':concerned_staff', $concernedStaff);
        $stmt->bindValue(':report_status', $input['statusSelect']);
        $rstatusReason = "The report is resolved.";
        $stmt->bindValue(':rstatus_reason', $rstatusReason);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->execute();

        $statusquery = "UPDATE cstatus SET concern_status = :concern_status, cstatus_reason = :cstatus_reason WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Resolved.";
        $cstatusReason = "The concern is resolved.";
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->execute();

        } else {

            // Start the transaction
            $db->beginTransaction();

            $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':report_id', $input['reportId']);
            $stmt->bindValue(':actions', $input['reportText']);
            $staff = 'Kagawad 2.';
            $stmt->bindValue(':staff', $staff);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $reportId = $db->lastInsertId();

            // Get the name value
            $name = 'Kagawad 2.';

            // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
            $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

            // Get the current date in the format YYYYMMDD
            $currentDate = date('Ymd'); // For example, 20250205

            // Define the base upload directory
            $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

            // Define the action report ID (you may want to get this dynamically or from the form input)
            $actionReportId = $reportId;

            // Define the final upload directory, using the structure provided
            $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

            // Ensure the directory for the report exists or create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
            }

            // Function to handle file upload and return the file path
            function handleFileUpload($file, $uploadDir, $reportId)
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
                    $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
            $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
            $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
            $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

            $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':action_id', $reportId);
            $stmt->bindValue(':afile1', $filePaths['file1']);
            $stmt->bindValue(':afile2', $filePaths['file2']);
            $stmt->bindValue(':afile3', $filePaths['file3']);
            $stmt->execute();

            $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
            $stmt = $db->prepare($query);
            $concernedStaff = 'Captain.';
            $stmt->bindValue(':concerned_staff', $concernedStaff);
            $stmt->bindValue(':report_status', $input['statusSelect']);
            $rstatusReason = "The report is not resolved.";
            $stmt->bindValue(':rstatus_reason', $rstatusReason);
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

/*************Kagawad 3*************/
$group->get('/k3fetchReports', function (Request $request, Response $response) use ($db) {
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
    rs.concerned_staff = 'Kagawad 3.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/k3search/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
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
    rs.concerned_staff = 'Kagawad 3.' AND s.store_name LIKE :searchTerm
    ORDER BY rs.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/k3fetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT 
    r.report_id, 
    r.concern_id,
    cc.anonymity_status,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    cu.file2,
    cu.file3,
    rs.report_status,
    rs.rstatus_reason,  
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
        rs.concerned_staff = 'Kagawad 1.' 
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

$group->post('/k3post', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];
        $image2 = $files['image2'];
        $image3 = $files['image3'];

        if($input['statusSelect'] == "Resolved."){
        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->bindValue(':actions', $input['reportText']);
        $staff = 'Kagawad 3.';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Kagawad 3.';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Get the current date in the format YYYYMMDD
        $currentDate = date('Ymd'); // For example, 20250205

        // Define the base upload directory
        $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

        // Define the action report ID (you may want to get this dynamically or from the form input)
        $actionReportId = $reportId;

        // Define the final upload directory, using the structure provided
        $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

        // Ensure the directory for the report exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Function to handle file upload and return the file path
        function handleFileUpload($file, $uploadDir, $reportId)
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
                $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
        $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
        $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
        $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

        $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':action_id', $reportId);
        $stmt->bindValue(':afile1', $filePaths['file1']);
        $stmt->bindValue(':afile2', $filePaths['file2']);
        $stmt->bindValue(':afile3', $filePaths['file3']);
        $stmt->execute();

        $query = "UPDATE storeviolations SET store_violations = :store_violations WHERE store_id = :store_id";
        $stmt = $db->prepare($query);
        $violation = "None";
        $stmt->bindValue(':store_violations', $violation);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->execute();

        $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
        $stmt = $db->prepare($query);
        $concernedStaff = "None";
        $stmt->bindValue(':concerned_staff', $concernedStaff);
        $stmt->bindValue(':report_status', $input['statusSelect']);
        $rstatusReason = "The report is resolved.";
        $stmt->bindValue(':rstatus_reason', $rstatusReason);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->execute();

        $statusquery = "UPDATE cstatus SET concern_status = :concern_status, cstatus_reason = :cstatus_reason WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Resolved.";
        $cstatusReason = "The concern is resolved.";
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->execute();

        } else {

            // Start the transaction
            $db->beginTransaction();

            $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':report_id', $input['reportId']);
            $stmt->bindValue(':actions', $input['reportText']);
            $staff = 'Kagawad 3.';
            $stmt->bindValue(':staff', $staff);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $reportId = $db->lastInsertId();

            // Get the name value
            $name = 'Kagawad 3.';

            // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
            $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

            // Get the current date in the format YYYYMMDD
            $currentDate = date('Ymd'); // For example, 20250205

            // Define the base upload directory
            $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

            // Define the action report ID (you may want to get this dynamically or from the form input)
            $actionReportId = $reportId;

            // Define the final upload directory, using the structure provided
            $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

            // Ensure the directory for the report exists or create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
            }

            // Function to handle file upload and return the file path
            function handleFileUpload($file, $uploadDir, $reportId)
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
                    $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
            $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
            $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
            $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

            $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':action_id', $reportId);
            $stmt->bindValue(':afile1', $filePaths['file1']);
            $stmt->bindValue(':afile2', $filePaths['file2']);
            $stmt->bindValue(':afile3', $filePaths['file3']);
            $stmt->execute();

            $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
            $stmt = $db->prepare($query);
            $concernedStaff = 'Captain.';
            $stmt->bindValue(':concerned_staff', $concernedStaff);
            $stmt->bindValue(':report_status', $input['statusSelect']);
            $rstatusReason = "The report is not resolved.";
            $stmt->bindValue(':rstatus_reason', $rstatusReason);
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

/*************Super admin*************/
$group->get('/sfetchReports', function (Request $request, Response $response) use ($db) {
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
    rs.concerned_staff = :staff";
    $stmt = $db->prepare($query);
    $svariable = $_SESSION['barangayRole'] . ".";
    $stmt->bindValue(':staff', $svariable);
    $stmt->execute();
    //$svariable = $_SESSION['profile'];
    //echo 'hello' . $svariable;
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

$group->get('/sfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT 
    r.report_id, 
    r.concern_id,
    CONCAT(cc.firstname, ' ', cc.middlename, ' ', cc.lastname) AS fullname, 
    r.report_details, 
    cu.file1,
    cu.file2,
    cu.file3,
    rs.report_status,
    rs.rstatus_reason,  
    rs.create_at, 
    s.store_id,
    s.store_name, 
    s.store_address,
    s.record_status, 
    v.store_violations,
    ar.actions,
    rf.afile1,
    rf.afile2,
    rf.afile3
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN 
        concernedcitizen ON cc.concern_id = r.concern_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    JOIN
        actionreports ar ON r.report_id = ar.report_id
    JOIN
        areportfiles rf ON ar.action_id= rf.action_id
    WHERE 
        rs.concerned_staff = 'Captain.' 
        AND r.report_id = :id
    ORDER BY 
        rs.create_at ASC";

    $stmt = $db->prepare($query);
    //$barangayRole =  $_SESSION['profile'];
    //echo 'hello' . $_SESSION['profile'];
    //$stmt->bindValue(':staff', $barangayRole);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/spost', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $image1 = $files['image1'];
        $image2 = $files['image2'];
        $image3 = $files['image3'];

        if($input['statusSelect'] == "Resolved."){
        // Start the transaction
        $db->beginTransaction();

        $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->bindValue(':actions', $input['reportText']);
        $staff = 'Captain.';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Captain';

        // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
        $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

        // Get the current date in the format YYYYMMDD
        $currentDate = date('Ymd'); // For example, 20250205

        // Define the base upload directory
        $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

        // Define the action report ID (you may want to get this dynamically or from the form input)
        $actionReportId = $reportId;

        // Define the final upload directory, using the structure provided
        $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

        // Ensure the directory for the report exists or create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
        }

        // Function to handle file upload and return the file path
        function handleFileUpload($file, $uploadDir, $reportId)
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
                $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
        $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
        $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
        $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

        $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':action_id', $reportId);
        $stmt->bindValue(':afile1', $filePaths['file1']);
        $stmt->bindValue(':afile2', $filePaths['file2']);
        $stmt->bindValue(':afile3', $filePaths['file3']);
        $stmt->execute();

        $query = "UPDATE storeviolations SET store_violations = :store_violations WHERE store_id = :store_id";
        $stmt = $db->prepare($query);
        $violation = "None";
        $stmt->bindValue(':store_violations', $violation);
        $stmt->bindValue(':store_id', $input['storeId']);
        $stmt->execute();

        $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
        $stmt = $db->prepare($query);
        $concernedStaff = "None";
        $stmt->bindValue(':concerned_staff', $concernedStaff);
        $stmt->bindValue(':report_status', $input['statusSelect']);
        $rstatusReason = "The report is resolved.";
        $stmt->bindValue(':rstatus_reason', $rstatusReason);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->execute();

        $statusquery = "UPDATE cstatus SET concern_status = :concern_status, cstatus_reason = :cstatus_reason WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Resolved.";
        $cstatusReason = "The concern is resolved.";
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->execute();

        } else {

            // Start the transaction
            $db->beginTransaction();

            $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':report_id', $input['reportId']);
            $stmt->bindValue(':actions', $input['reportText']);
            $staff = 'Captain.';
            $stmt->bindValue(':staff', $staff);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $reportId = $db->lastInsertId();

            // Get the name value
            $name = 'Captain';

            // Sanitize the name to ensure it's a valid folder name (no spaces or special characters)
            $name = preg_replace("/[^a-zA-Z0-9-_]/", "_", $name);

            // Get the current date in the format YYYYMMDD
            $currentDate = date('Ymd'); // For example, 20250205

            // Define the base upload directory
            $baseUploadDir = '../../uploads/actionreports/' . $name . '/';

            // Define the action report ID (you may want to get this dynamically or from the form input)
            $actionReportId = $reportId;

            // Define the final upload directory, using the structure provided
            $uploadDir = $baseUploadDir . $currentDate . "_" . $actionReportId . "/";

            // Ensure the directory for the report exists or create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
            }

            // Function to handle file upload and return the file path
            function handleFileUpload($file, $uploadDir, $reportId)
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
                    $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

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
            $filePaths['file1'] = handleFileUpload($image1, $uploadDir, $reportId);
            $filePaths['file2'] = handleFileUpload($image2, $uploadDir, $reportId);
            $filePaths['file3'] = handleFileUpload($image3, $uploadDir, $reportId);

            $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':action_id', $reportId);
            $stmt->bindValue(':afile1', $filePaths['file1']);
            $stmt->bindValue(':afile2', $filePaths['file2']);
            $stmt->bindValue(':afile3', $filePaths['file3']);
            $stmt->execute();

            $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
            $stmt = $db->prepare($query);
            $concernedStaff = 'Cityhall.';
            $stmt->bindValue(':concerned_staff', $concernedStaff);
            $stmt->bindValue(':report_status', $input['statusSelect']);
            $rstatusReason = "The report is not resolved.";
            $stmt->bindValue(':rstatus_reason', $rstatusReason);
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

/*************City hall*************/
$group->get('/ctfetchReports', function (Request $request, Response $response) use ($db) {
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
    rs.concerned_staff = 'Cityhall.' AND rs.report_status = 'Forward to cityhall.'";
    $stmt = $db->prepare($query);
    //$svariable = $_SESSION['profile'];
    //$stmt->bindValue(':staff', $svariable);
    $stmt->execute();
    //$svariable = $_SESSION['profile'];
    //echo 'hello' . $svariable;
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/ctsearch/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
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
    rs.concerned_staff = 'Cityhall.' AND s.store_name LIKE :searchTerm
    ORDER BY rs.create_at ASC";

    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/ctfetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT 
    r.report_id, 
    r.concern_id, 
    r.report_details, 
    cu.file1,
    cu.file2,
    cu.file3,
    rs.report_status,
    rs.rstatus_reason,  
    rs.create_at, 
    s.store_id,
    s.store_name, 
    s.store_address, 
    v.store_violations
    FROM 
        stores s
    JOIN 
        reports r ON s.store_id = r.store_id
    JOIN
        concernsuploads cu ON r.concern_id = cu.concern_id
    JOIN
        rstatus rs ON r.report_id = rs.report_id
    LEFT JOIN 
        storeviolations v ON s.store_id = v.store_id  -- Join violations table
    WHERE 
        rs.concerned_staff = 'Cityhall.' 
        AND r.report_id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT ar.actions AS k_action, rf.afile1 AS k_file1, rf.afile2 AS k_file2, rf.afile3 As k_file3
        FROM actionreports ar 
        JOIN
            areportfiles rf ON ar.action_id= rf.action_id
        WHERE ar.staff LIKE '%Kagawad%' AND ar.report_id = :id;
        ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $krfiles = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT ar.actions AS c_action, rf.afile1 AS c_file1, rf.afile2 AS c_file2, rf.afile3 AS c_file3
        FROM actionreports ar 
        JOIN
            areportfiles rf ON ar.action_id= rf.action_id
        WHERE ar.staff LIKE '%Captain%' AND ar.report_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $crfiles = $stmt->fetch(PDO::FETCH_ASSOC);

    // Combine both results into a single associative array
    $responseData = array_merge($report, $krfiles, $crfiles);

    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/ctpost', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();

        /* $query = "INSERT INTO actionreports (report_id, actions, staff) VALUES (:report_id, :actions, :staff)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->bindValue(':actions', $input['reportText']);
        $staff = 'Captain.';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute(); */

        // Get the last inserted user ID (for linking to the order)
        //$reportId = $db->lastInsertId();

        /* $query = "INSERT INTO areportfiles (action_id, afile1, afile2, afile3) VALUES (:action_id, :afile1, :afile2, :afile3)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':action_id', $reportId);
        $filePaths1 = 'Not required.';
        $filePaths2 = 'Not required.';
        $filePaths3 = 'Not required.';
        $stmt->bindValue(':afile1', $filePaths1);
        $stmt->bindValue(':afile2', $filePaths1);
        $stmt->bindValue(':afile3', $filePaths1);
        $stmt->execute(); */

        $query = "UPDATE rstatus SET concerned_staff = :concerned_staff, report_status = :report_status, rstatus_reason = :rstatus_reason WHERE report_id = :report_id";
        $stmt = $db->prepare($query);
        $concernedStaff = "Cityhall.";
        $stmt->bindValue(':concerned_staff', $concernedStaff);
        $stmt->bindValue(':report_status', $input['reportStatus']);
        $stmt->bindValue(':rstatus_reason', $input['statusReason']);
        $stmt->bindValue(':report_id', $input['reportId']);
        $stmt->execute();

        /* $statusquery = "UPDATE cstatus SET concern_status = :concern_status, cstatus_reason = :cstatus_reason WHERE concern_id = :concern_id";
        $statusstmt = $db->prepare($statusquery);
        $concernId = $input['concernId'];
        $concernStatus = "Resolved.";
        $cstatusReason = "The concern is resolved.";
        $statusstmt->bindParam(':concern_status', $concernStatus);
        $statusstmt->bindParam(':cstatus_reason', $cstatusReason);
        $statusstmt->bindParam(':concern_id', $concernId);
        $statusstmt->execute(); */
        
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

/*************Updates *************/
$group->get('/reportUpdates', function (Request $request, Response $response) use ($db) {
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
    rs.concerned_staff = 'Cityhall.' AND rs.report_status = 'Forwarded to cityhall.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/updatesSearch/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
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
    rs.concerned_staff = 'Cityhall.' AND rs.report_status = 'Forwarded to cityhall.' AND s.store_name LIKE :searchTerm
    ORDER BY rs.create_at ASC";
    $stmt = $db->prepare($query);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/*************Clearing operations ************/
$group->get('/clrngopsPending', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search == null){
    $query = "SELECT 
        YEAR(operation_date) AS year,
        DATE_FORMAT(operation_date, '%Y-%m') AS `year_month`,
        MONTH(operation_date) AS month_number,
        MONTHNAME(operation_date) AS month,
        status,
        COUNT(*) AS report_count
        FROM clearingoperations
        WHERE status = 'Pending.'
        GROUP BY year, `year_month`, month_number, month, status
        ORDER BY year, month_number";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }else {
    $query = "SELECT 
        YEAR(operation_date) AS year,
        DATE_FORMAT(operation_date, '%Y-%m') AS `year_month`,
        MONTH(operation_date) AS month_number,
        MONTHNAME(operation_date) AS month,
        status,
        COUNT(*) AS report_count
        FROM clearingoperations
        WHERE status = 'Pending.' AND MONTHNAME(operation_date) LIKE :searchTerm
        GROUP BY year, `year_month`, month_number, month, status
        ORDER BY year, month_number";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/clrngopsSent', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search == null){
    $query = "SELECT 
        YEAR(operation_date) AS year,
        DATE_FORMAT(operation_date, '%Y-%m') AS `year_month`,
        MONTH(operation_date) AS month_number,
        MONTHNAME(operation_date) AS month,
        status,
        COUNT(*) AS report_count
        FROM clearingoperations
        WHERE status = 'Sent.'
        GROUP BY year, `year_month`, month_number, month, status
        ORDER BY year, month_number";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }else {
    $query = "SELECT 
        YEAR(operation_date) AS year,
        DATE_FORMAT(operation_date, '%Y-%m') AS `year_month`,
        MONTH(operation_date) AS month_number,
        MONTHNAME(operation_date) AS month,
        status,
        COUNT(*) AS report_count
        FROM clearingoperations
        WHERE status = 'Sent.' AND MONTHNAME(operation_date) LIKE :searchTerm
        GROUP BY year, `year_month`, month_number, month, status
        ORDER BY year, month_number";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/clrngopsFetch/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT clr.clrngops_id, clr.title, clr.date, clr.time, clr.staff, clr.details, clf.before_file1, clf.before_file2, clf.before_file3, clf.after_file1, clf.after_file2, clf.after_file3
        FROM clrngopsrprt clr
        JOIN clrngopsfiles clf ON clr.clrngops_id = clf.clrngops_id
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE clr.clrngops_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/clrngopsreport', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        $beforeimage1 = $files['beforeimage1'];
        $beforeimage2 = $files['beforeimage2'];
        $beforeimage3 = $files['beforeimage3'];
        $afterimage1 = $files['afterimage1'];
        $afterimage2 = $files['afterimage2'];
        $afterimage3 = $files['afterimage3'];

         // Start the transaction
         $db->beginTransaction();

        $query = "INSERT INTO clrngopsrprt(title, date, time, staff, details) VALUES (:title, :date, :time, :staff, :details)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':title', $input['title']);
        $stmt->bindValue(':date', $input['date']);
        $stmt->bindValue(':time', $input['time']);
        $stmt->bindValue(':staff', $input['staff']);
        $stmt->bindValue(':details', $input['details']); 
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the current date in the format YYYYMMDD
        //$currentDate = date('Ymd'); // For example, 20250205

        // Define the base upload directory
        //$baseUploadDir = '../../uploads/clrngops/';

        // Define the final upload directory, using the structure provided
        // We will modify this to include 'before' or 'after' based on the passed parameter
        function handleFileUpload($file, $reportId, $folder)
        {
            // Get the current date in the format YYYYMMDD
            $currentDate = date('Ymd'); // For example, 20250205

            // Define the base upload directory
            $baseUploadDir = '../../uploads/clrngops/';

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

                // Generate a new filename using the report ID and a random number
                $randomNumber = mt_rand(1000, 9999); // Generate a random number between 1000 and 9999
                $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION); // Use the extension of the original file

                // Define the destination path (file will be saved in 'uploads/clrngops/YYYYMMDD_actionreportid/before/file' or 'after/file')
                $uploadDir = $baseUploadDir . $currentDate . "_" . $reportId . "/" . $folder . "/"; // Ensure 'before' or 'after' is appended correctly

                // Ensure the directory for the report exists or create it
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // Create the folder with write permissions if it doesn't exist
                }

                // Define the destination path
                $destination = $uploadDir . $newFileName;

                // Move the file to the destination folder
                $file->moveTo($destination);

                // Return the relative path (for example, 'uploads/clrngops/YYYYMMDD_actionreportid/before/file1234.jpg')
                // Remove the leading '../../' from the path (if needed, depending on the context)
                if (substr($destination, 0, 6) === "../../") {
                    $destination = substr($destination, 6); // Remove the first 6 characters
                }
                return $destination;
            }

            // Return error if file is not uploaded properly
            return "No file uploaded.";
        }

        // Upload files and collect their paths, passing 'before' or 'after' as needed
        $filePaths = [];
        $filePaths['beforefile1'] = handleFileUpload($beforeimage1, $reportId, 'before');
        $filePaths['beforefile2'] = handleFileUpload($beforeimage2, $reportId, 'before');
        $filePaths['beforefile3'] = handleFileUpload($beforeimage3, $reportId, 'before');
        $filePaths['afterfile1'] = handleFileUpload($afterimage1, $reportId, 'after');
        $filePaths['afterfile2'] = handleFileUpload($afterimage2, $reportId, 'after');
        $filePaths['afterfile3'] = handleFileUpload($afterimage3, $reportId, 'after');

        $query = "INSERT INTO clrngopsfiles (clrngops_id, before_file1, before_file2, before_file3, after_file1, after_file2, after_file3) VALUES (:clrngops_id, :before_file1, :before_file2, :before_file3, :after_file1, :after_file2, :after_file3)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':clrngops_id', $reportId);
        $stmt->bindValue(':before_file1', $filePaths['beforefile1']);
        $stmt->bindValue(':before_file2', $filePaths['beforefile2']);
        $stmt->bindValue(':before_file3', $filePaths['beforefile3']);
        $stmt->bindValue(':after_file1', $filePaths['afterfile1']);
        $stmt->bindValue(':after_file2', $filePaths['afterfile2']);
        $stmt->bindValue(':after_file3', $filePaths['afterfile3']);
        $stmt->execute();

        $query = "INSERT INTO clrngopsstatus (clrngops_id, clrngops_status) VALUES (:clrngops_id, :clrngops_status)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':clrngops_id', $reportId);
        $clrngopsstatus = 'Pending.';
        $stmt->bindValue(':clrngops_status', $clrngopsstatus);
        $stmt->execute();

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

$group->post('/clrngopsUpdate', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();

        //update status
        $statusquery = "UPDATE clearingoperations SET status = :status WHERE DATE_FORMAT(operation_date, '%Y-%m') = :year_month";
        $statusstmt = $db->prepare($statusquery);
        $statusstmt->bindValue(':status', $input['status']);
        $statusstmt->bindValue(':year_month', $input['year_month']);
        $statusstmt->execute();
        
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

/*************Tracking *************/
$group->get('/Violations', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "
    SELECT s.store_name, s.store_address,
        SUM(CASE WHEN vt.store_violation LIKE '%EXPIRED PRODUCTS%' THEN 1 ELSE 0 END) AS expired_products_count,
        SUM(CASE WHEN vt.store_violation LIKE '%UNHYGIENIC CONDITIONS%' THEN 1 ELSE 0 END) AS unhygienic_conditions_count,
        SUM(CASE WHEN vt.store_violation LIKE '%INCORRECT LABELLING%' THEN 1 ELSE 0 END) AS incorrect_labelling_count,
        SUM(CASE WHEN vt.store_violation LIKE '%OVERPRICING%' THEN 1 ELSE 0 END) AS overpricing_count,
        SUM(CASE WHEN vt.store_violation LIKE '%UNSANITARY STORAGE%' THEN 1 ELSE 0 END) AS unsanitary_storage_count,
        SUM(CASE WHEN vt.store_violation LIKE '%MISLEADING ADVERTISEMENT%' THEN 1 ELSE 0 END) AS misleading_advertisement_count,
        SUM(CASE WHEN vt.store_violation LIKE '%IMPROPER PACKAGING%' THEN 1 ELSE 0 END) AS improper_packaging_count,
        SUM(CASE WHEN vt.store_violation LIKE '%LACK OF PROPER LICENSE%' THEN 1 ELSE 0 END) AS lack_of_proper_license_count,
        SUM(CASE WHEN vt.store_violation LIKE '%UNSAFE FOOD HANDLING%' THEN 1 ELSE 0 END) AS unsafe_food_handling_count,
        SUM(CASE WHEN vt.store_violation LIKE '%None%' THEN 1 ELSE 0 END) AS no_violations
    FROM stores s
    JOIN storeviolations sv ON s.store_id = sv.store_Id
    JOIN violationstrail vt ON sv.sviolation_id = vt.sviolation_id
    WHERE s.store_name LIKE :searchTerm
    GROUP BY s.store_name, s.store_address
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "
        SELECT s.store_name, s.store_address,
            SUM(CASE WHEN vt.store_violation LIKE '%EXPIRED PRODUCTS%' THEN 1 ELSE 0 END) AS expired_products_count,
            SUM(CASE WHEN vt.store_violation LIKE '%UNHYGIENIC CONDITIONS%' THEN 1 ELSE 0 END) AS unhygienic_conditions_count,
            SUM(CASE WHEN vt.store_violation LIKE '%INCORRECT LABELLING%' THEN 1 ELSE 0 END) AS incorrect_labelling_count,
            SUM(CASE WHEN vt.store_violation LIKE '%OVERPRICING%' THEN 1 ELSE 0 END) AS overpricing_count,
            SUM(CASE WHEN vt.store_violation LIKE '%UNSANITARY STORAGE%' THEN 1 ELSE 0 END) AS unsanitary_storage_count,
            SUM(CASE WHEN vt.store_violation LIKE '%MISLEADING ADVERTISEMENT%' THEN 1 ELSE 0 END) AS misleading_advertisement_count,
            SUM(CASE WHEN vt.store_violation LIKE '%IMPROPER PACKAGING%' THEN 1 ELSE 0 END) AS improper_packaging_count,
            SUM(CASE WHEN vt.store_violation LIKE '%LACK OF PROPER LICENSE%' THEN 1 ELSE 0 END) AS lack_of_proper_license_count,
            SUM(CASE WHEN vt.store_violation LIKE '%UNSAFE FOOD HANDLING%' THEN 1 ELSE 0 END) AS unsafe_food_handling_count,
            SUM(CASE WHEN vt.store_violation LIKE '%None%' THEN 1 ELSE 0 END) AS no_violations
        FROM stores s
        JOIN storeviolations sv ON s.store_id = sv.store_Id
        JOIN violationstrail vt ON sv.sviolation_id = vt.sviolation_id
        GROUP BY s.store_name, s.store_address
        ";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/Concerns', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
            WHERE s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id";
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
    $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        WHERE cs.concern_status ='No action.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
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
    $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        WHERE cs.concern_status ='Reported.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
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
    $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
        FROM concerns c 
        JOIN cstatus cs ON c.concern_id = cs.concern_id
        JOIN stores s ON c.store_id = s.store_id
        WHERE cs.concern_status ='Resolved.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT c.concern_id, cs.concern_status, s.store_name, s.store_address, c.concern_details
            FROM concerns c 
            JOIN cstatus cs ON c.concern_id = cs.concern_id
            JOIN stores s ON c.store_id = s.store_id
            WHERE cs.concern_status ='Resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/sfetchReportsStatus', function (Request $request, Response $response) use ($db) {
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id";
    $stmt = $db->prepare($query);
    //$svariable = $_SESSION['profile'];
    //$stmt->bindValue(':staff', $svariable);
    $stmt->execute();
    //$svariable = $_SESSION['profile'];
    //echo 'hello' . $svariable;
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/Reports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE s.store_name LIKE :searchTerm";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
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

$group->get('/KagawadReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status ='For kagawad.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, rs.report_status, s.store_name, s.store_address, r.report_details
            FROM reports r 
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN stores s ON r.store_id = s.store_id
            WHERE rs.report_status ='For kagawad.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/CaptainReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status ='For captain.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status ='For captain.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/CityReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status ='For cityhall.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status ='For cityhall.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/FrwrdCityReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status ='Forwarded to cityhall.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status ='Forwarded to cityhall.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/ResolvedReports', function (Request $request, Response $response) use ($db) {
    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
        FROM reports r
        JOIN concerns c ON r.concern_id = c.concern_id
        JOIN stores s ON c.store_id = s.store_id 
        JOIN rstatus rs ON r.report_id = rs.report_id
        WHERE rs.report_status ='Resolved.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status ='Resolved.'";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/InfoTally', function (Request $request, Response $response) use ($db) {
    $query = "
    SELECT
        SUM(CASE WHEN vt.store_violation LIKE '%EXPIRED PRODUCTS%' THEN 1 ELSE 0 END) AS expired_products_count,
        SUM(CASE WHEN vt.store_violation LIKE '%UNHYGIENIC CONDITIONS%' THEN 1 ELSE 0 END) AS unhygienic_conditions_count,
        SUM(CASE WHEN vt.store_violation LIKE '%INCORRECT LABELLING%' THEN 1 ELSE 0 END) AS incorrect_labelling_count,
        SUM(CASE WHEN vt.store_violation LIKE '%OVERPRICING%' THEN 1 ELSE 0 END) AS overpricing_count,
        SUM(CASE WHEN vt.store_violation LIKE '%UNSANITARY STORAGE%' THEN 1 ELSE 0 END) AS unsanitary_storage_count,
        SUM(CASE WHEN vt.store_violation LIKE '%MISLEADING ADVERTISEMENT%' THEN 1 ELSE 0 END) AS misleading_advertisement_count,
        SUM(CASE WHEN vt.store_violation LIKE '%IMPROPER PACKAGING%' THEN 1 ELSE 0 END) AS improper_packaging_count,
        SUM(CASE WHEN vt.store_violation LIKE '%LACK OF PROPER LICENSE%' THEN 1 ELSE 0 END) AS lack_of_proper_license_count,
        SUM(CASE WHEN vt.store_violation LIKE '%UNSAFE FOOD HANDLING%' THEN 1 ELSE 0 END) AS unsafe_food_handling_count,
        SUM(CASE WHEN vt.store_violation LIKE '%None%' THEN 1 ELSE 0 END) AS no_violations
    FROM stores s
    JOIN storeviolations sv ON s.store_id = sv.store_Id
    JOIN violationstrail vt ON sv.sviolation_id = vt.sviolation_id
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $violationdata = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(store_id) AS store_count FROM `stores`";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $storedata = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(report_status) AS resolved_report FROM rstatus WHERE report_status = 'Resolved.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resolvedreport = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(report_status) AS fwrdcity_report FROM rstatus WHERE report_status = 'Forwarded to cityhall.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $frwdcityreport = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT 
        SUM(CASE WHEN report_status = 'For kagawad.' THEN 1 ELSE 0 END) AS forkagawad_count,
        SUM(CASE WHEN report_status = 'For captain.' THEN 1 ELSE 0 END) AS forcaptain_count,
        SUM(CASE WHEN report_status = 'Forwarded to cityhall.' THEN 1 ELSE 0 END) AS fwrdcityhall_count,
        SUM(CASE WHEN report_status = 'Resolved.' THEN 1 ELSE 0 END) AS resolved_count
    FROM rstatus;
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reporttally = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(clr.clrngops_id) AS clrngops_count FROM clrngopsrprt clr 
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Sent.' ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clrngopstally = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine both results into a single associative array
    $responseData = array_merge($violationdata, $storedata, $resolvedreport, $frwdcityreport, $reporttally, $clrngopstally);


    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/sessiontest', function (Request $request, Response $response) use ($db) {
    // Setting the session variable somewhere (e.g., after login)
    //$_SESSION["id"] = '115';
    // Check if the session value exists
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];  // Get session value
        $username = $_SESSION['username'];  // Get session value
        $profile = $_SESSION['profile'];  // Get session value
        $barangayRole = $_SESSION['barangayRole'];  // Get session value
        $status = $_SESSION['status'];  // Get session value
        $picture = $_SESSION['picture'];  // Get session value
    } else {
        $data = "Session 'id' not found.";  // If not found
    }
    
    // Combine both results into a single associative array
    $responseData = array_merge($id, $username, $profile, $barangayRole, $status, $picture);
    //$data = $_SESSION["id"];
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchactivity', function (Request $request, Response $response) use ($db) {
    
    $query = "
        SELECT  
            a.id AS activity_id,
            a.conducted_activity,
            a.date,
            a.action_taken,
            a.remarks,
            s.street_name,
            s.road_length
        FROM streets s
        JOIN activities a ON s.activity_id = a.id
        WHERE a.id = '3'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

/*
// Helper function to save uploaded files
function saveUploadedFile($uploadedFile, $directory)
{
    // Get the file extension and generate a unique name for the file
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;

    // Move the uploaded file to the target directory
    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

// Save activity endpoint
$group->post('/save_activity', function (Request $request, Response $response) use ($db) {
    $data = json_decode($request->getBody(), true);

    // Define the directory where uploaded photos will be saved
    $uploadDirectory = '../../uploads/safetymonitoring/';  // Set your actual uploads directory

    // Get the uploaded files from the form
    $uploadedFiles = $request->getUploadedFiles();
    $streetPhotos = []; // Array to hold the file names for street photos
    $barcoPhotoName = null;

    // Process street photos (assuming there are up to 3 photos per street)
    foreach ($data['clearing_operations'][0]['streets'] as $index => $street) {
        $photos = $street['photos'] ?? [];
        $savedPhotos = [];
        foreach ($photos as $photoIndex => $photo) {
            // Check if the file exists in the uploaded files
            $uploadedFile = $uploadedFiles['streets'][$index]['photos'][$photoIndex] ?? null;
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
                $savedPhotos[] = saveUploadedFile($uploadedFile, $uploadDirectory);
            }
        }
        $streetPhotos[] = $savedPhotos; // Store the saved filenames for each street
    }

    // Process the BaRCO participant photo
    $barcoPhotoFile = $uploadedFiles['barco_photo'] ?? null;
    if ($barcoPhotoFile && $barcoPhotoFile->getError() === UPLOAD_ERR_OK) {
        $barcoPhotoName = saveUploadedFile($barcoPhotoFile, $uploadDirectory);
    }

    // Begin transaction to ensure everything is saved atomically
    $db->beginTransaction();
    try {
        // Insert clearing operation
        $stmt = $db->prepare('INSERT INTO clearing_operations (conducted, action_taken, remarks) VALUES (?, ?, ?)');
        $stmt->execute([$data['clearing_operations'][0]['clearing_conducted'], $data['clearing_operations'][0]['action_taken'], $data['clearing_operations'][0]['remarks']]);
        $clearingOpId = $db->lastInsertId();

        // Insert streets
        foreach ($data['clearing_operations'][0]['streets'] as $index => $street) {
            $stmt = $db->prepare('INSERT INTO streets (clearing_operation_id, street_name, road_length, photo1, photo2, photo3) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $clearingOpId,
                $street['street_name'],
                $street['road_length'],
                $streetPhotos[$index][0] ?? null, // Save first photo
                $streetPhotos[$index][1] ?? null, // Save second photo
                $streetPhotos[$index][2] ?? null  // Save third photo
            ]);
        }

        // Insert BaRCO participants
        $stmt = $db->prepare('INSERT INTO barco_participants (barangay_official, sk_official, barangay_tanod, barco_photo) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['barco_participants']['barangay_official'],
            $data['barco_participants']['sk_official'],
            $data['barco_participants']['barangay_tanod'],
            $barcoPhotoName
        ]);

        // Commit the transaction
        $db->commit();

        // Send success response
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\Exception $e) {
        // Rollback transaction in case of error
        $db->rollBack();
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}); */

// Function to handle file upload and return the file path
function handleFileUpload($file, $uploadDir, $reportId)
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
        $fileName = $file->getClientFilename();
        $fileSize = $file->getSize();
        $fileType = $file->getClientMediaType();
        $fileError = $file->getError();

        if ($fileError !== UPLOAD_ERR_OK) {
            error_log("Error uploading file $fileName. Error code: $fileError");
            return null;
        }

        if (!in_array($fileType, $allowedFileTypes)) {
            error_log("Invalid file type for file $fileName. Only .jpeg, .png, .pdf, .doc, .docx allowed.");
            return null;
        }

        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $randomNumber = mt_rand(1000, 9999);
        $newFileName = $reportId . "_" . $randomNumber . "." . pathinfo($fileName, PATHINFO_EXTENSION);
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $newFileName;

        $file->moveTo($destination);

        return $newFileName; // Or return relative path if needed
    }

    return null;
}

$group->post('/save_activity', function (Request $request, Response $response) use ($db) {
    $formData = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

    try {
        $db->beginTransaction();

        $barangayOfficial = $formData['barangay_official'] ?? 0;
        $skOfficial = $formData['sk_official'] ?? 0;
        $barangayTanod = $formData['barangay_tanod'] ?? 0;

        // First, safely extract the date string
        $clearingDate = $formData['clearing_date'][0];

        // Now safely use strtotime
        $barcoDateFolder = date('Ym', strtotime($clearingDate));  // e.g. 202505

        $barcoPhotoPath = null;
        if (!empty($uploadedFiles['barco_photo']) && $uploadedFiles['barco_photo']->getError() === UPLOAD_ERR_OK) {
            $uploadFolder = "../../uploads/safetymonitoring/barco_$barcoDateFolder";
            $pathFolder = "uploads/safetymonitoring/barco_$barcoDateFolder";
            if (!is_dir($uploadFolder)) mkdir($uploadFolder, 0777, true);

            $photoFile = $uploadedFiles['barco_photo'];
            $filename = uniqid() . '_' . $photoFile->getClientFilename();
            $filePath = "$uploadFolder/$filename";
            $photoFile->moveTo($filePath);

            $barcoPhotoPath = "$pathFolder/$filename";  // full relative path
        }

        $stmt = $db->prepare("INSERT INTO barcoinfo (barangay_official, sk_official, barangay_tanod, barco_photo_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$barangayOfficial, $skOfficial, $barangayTanod, $barcoPhotoPath]);
        $barcoId = $db->lastInsertId();

        foreach ($formData['clearing_date'] as $index => $clearingDate) {
            $conducted = $formData["clearing_conducted_$index"] ?? 'No';
            $actionTaken = $formData['clearing_action_taken'][$index] ?? '';
            $remarks = $formData['clearing_remarks'][$index] ?? '';

            // Insert clearing operation data
            $stmt = $db->prepare("INSERT INTO clearingoperations (operation_date, conducted, action_taken, remarks, barco_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$clearingDate, $conducted, $actionTaken, $remarks, $barcoId]);
            $operationId = $db->lastInsertId();

            // Create base path with date folder
            $baseUploadDir = __DIR__ . "/../../uploads/safetymonitoring/";
            $operationFolder = $baseUploadDir . $clearingDate;
            if (!is_dir($operationFolder)) mkdir($operationFolder, 0777, true);

            $streetNames = $formData["street_name_$index"] ?? [];
            $roadLengths = $formData["road_length_$index"] ?? [];

            foreach ($streetNames as $sIdx => $streetName) {
                $roadLength = $roadLengths[$sIdx] ?? '';
            
                // Sanitize folder name
                $safeStreetName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $streetName);
                $streetFolder = "$operationFolder/$safeStreetName";
                if (!is_dir($streetFolder)) mkdir($streetFolder, 0777, true);
            
                $photoField = "clearing_photos_{$index}_{$sIdx}";
                $filePaths = [null, null, null]; // up to 3
            
                if (!empty($uploadedFiles[$photoField])) {
                    $photoFiles = array_slice($uploadedFiles[$photoField], 0, 3); // Limit to 3
                    foreach ($photoFiles as $i => $file) {
                        if ($file->getError() === UPLOAD_ERR_OK) {
                            $filename = uniqid() . '_' . $file->getClientFilename();
                            $filePath = "$streetFolder/$filename";
                            $file->moveTo($filePath);
            
                            $relativePath = "uploads/safetymonitoring/$clearingDate/$safeStreetName/$filename";
                            $filePaths[$i] = $relativePath;
                        }
                    }
                }
            
                // Insert street with up to 3 image paths
                $stmt = $db->prepare("INSERT INTO clearingstreets (operation_id, street_name, road_length, file_path1, file_path2, file_path3) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$operationId, $streetName, $roadLength, $filePaths[0], $filePaths[1], $filePaths[2]]);
            }            
        }

        $db->commit();
        $response->getBody()->write(json_encode(['success' => true]));
    } catch (Exception $e) {
        $db->rollBack();
        $response->getBody()->write(json_encode(['success' => false, 'message' => $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/reporttable/{year_month}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT co.operation_date, co.conducted, co.action_taken, co.remarks, cs.street_name, cs.road_length
    FROM clearingoperations co
    JOIN clearingstreets cs ON co.id = cs.operation_id
    WHERE DATE_FORMAT(co.operation_date, '%Y-%m') = :year_month";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':year_month', $args['year_month'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/reportstreets/{year_month}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT co.operation_date,cs.street_name, cs.file_path1, cs.file_path2, cs.file_path3
        FROM clearingoperations co
        JOIN clearingstreets cs ON co.id = cs.operation_id
        WHERE DATE_FORMAT(co.operation_date, '%Y-%m') = :year_month";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':year_month', $args['year_month'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/reportinfo/{year_month}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT co.operation_date, bi.barangay_official, bi.sk_official, bi.barangay_tanod, bi.barco_photo_path
        FROM clearingoperations co
        JOIN barcoinfo bi ON co.barco_id = bi.id
        WHERE DATE_FORMAT(co.operation_date, '%Y-%m') = :year_month";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':year_month', $args['year_month'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/reportactivity', function (Request $request, Response $response) use ($db) {
    $query = "SELECT bi.barangay_official, bi.sk_official, bi.barangay_tanod, bi.barco_photo_path
        FROM barcoinfo bi
        JOIN clearingoperations co ON bi.operation_id = co.id
        WHERE co.operation_date = '2025-04-16'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/business', function (Request $request, Response $response) use ($db) {
    $url = "https://businesspermit.unifiedlgu.com/admin/business_approve_data_list_table.php";
    
    // Fetch the external data
    $data = file_get_contents($url);
    
    // Write it to the response body directly (assuming it's already JSON)
    $response->getBody()->write(json_encode($data));
    
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/census', function (Request $request, Response $response) use ($db) {
    $url = "https://backend-api-5m5k.onrender.com/api/cencus";
    
    // Fetch the external data
    $data = file_get_contents($url);
    
    // Write it to the response body directly (assuming it's already JSON)
    $response->getBody()->write(json_encode($data));
    
    return $response->withHeader('Content-Type', 'application/json');
});