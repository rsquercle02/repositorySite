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
    s.store_id,
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

$group->get('/fetchDetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    c.concern_id,
    s.store_id,
    s.store_name,
    s.store_address,
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
    rs.concerned_staff = 'Kagawad 1'";
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
    rs.concerned_staff = 'Kagawad 1' AND s.store_name LIKE :searchTerm
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
        rs.concerned_staff = 'Kagawad 1' 
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
        $staff = 'Kagawad 1';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Kagawad 1';

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
            $staff = 'Kagawad 1';
            $stmt->bindValue(':staff', $staff);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $reportId = $db->lastInsertId();

            // Get the name value
            $name = 'Kagawad 1';

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
    rs.concerned_staff = 'Kagawad 2'";
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
    rs.concerned_staff = 'Kagawad 2' AND s.store_name LIKE :searchTerm
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
        rs.concerned_staff = 'Kagawad 2' 
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
        $staff = 'Kagawad 2';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Kagawad 2';

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
            $staff = 'Kagawad 2';
            $stmt->bindValue(':staff', $staff);
            $stmt->execute();

            // Get the last inserted user ID (for linking to the order)
            $reportId = $db->lastInsertId();

            // Get the name value
            $name = 'Kagawad 2';

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
    rs.concerned_staff = 'Kagawad 3'";
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
    rs.concerned_staff = 'Kagawad 3' AND s.store_name LIKE :searchTerm
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
        rs.concerned_staff = 'Kagawad 3' 
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
        $staff = 'Kagawad 3';
        $stmt->bindValue(':staff', $staff);
        $stmt->execute();

        // Get the last inserted user ID (for linking to the order)
        $reportId = $db->lastInsertId();

        // Get the name value
        $name = 'Kagawad 3';

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
            $name = 'Kagawad 3';

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
    rs.concerned_staff = 'Captain.'";
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
    $query = "SELECT clr.clrngops_id, clr.title, clr.date, cls.clrngops_status, clr.create_at  FROM clrngopsrprt clr
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Pending.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }else {
    $query = "SELECT clr.clrngops_id, clr.title, clr.date, cls.clrngops_status, clr.create_at  FROM clrngopsrprt clr
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Pending.' AND clr.title LIKE :searchTerm";
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
    $query = "SELECT clr.clrngops_id, clr.title, clr.date, cls.clrngops_status, clr.create_at  FROM clrngopsrprt clr
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Sent.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }else {
    $query = "SELECT clr.clrngops_id, clr.title, clr.date, cls.clrngops_status, clr.create_at  FROM clrngopsrprt clr
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Sent.' AND clr.title LIKE :searchTerm";
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
        $statusquery = "UPDATE clrngopsstatus SET clrngops_status = :clrngops_status WHERE clrngops_id = :clrngops_id";
        $statusstmt = $db->prepare($statusquery);
        $statusstmt->bindParam(':clrngops_status', $input['status']);
        $statusstmt->bindParam(':clrngops_id', $input['clrreportId']);
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

$group->get('/NoactionReports', function (Request $request, Response $response) use ($db) {
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
        WHERE rs.report_status ='No action.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, rs.report_status, s.store_name, s.store_address, r.report_details
            FROM reports r 
            JOIN rstatus rs ON r.report_id = rs.report_id
            JOIN stores s ON r.store_id = s.store_id
            WHERE rs.report_status ='No action.'";
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
        WHERE rs.report_status ='Forward to captain.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status ='Forward to captain.'";
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
        WHERE rs.report_status ='Forward to cityhall.' AND s.store_name LIKE :searchTerm";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    } else {
        $query = "SELECT r.report_id, c.concern_id, s.store_name, s.store_address, rs.report_status, rs.concerned_staff, r.report_details
            FROM reports r
            JOIN concerns c ON r.concern_id = c.concern_id
            JOIN stores s ON c.store_id = s.store_id 
            JOIN rstatus rs ON r.report_id = rs.report_id
            WHERE rs.report_status ='Forward to cityhall.'";
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

$group->get('/ViolationTally', function (Request $request, Response $response) use ($db) {
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

    $query = "SELECT COUNT(report_status) AS fwrdcity_report FROM rstatus WHERE report_status = 'Forward to cityhall.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $frwdcityreport = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT 
        SUM(CASE WHEN report_status = 'No action.' THEN 1 ELSE 0 END) AS noaction_count,
        SUM(CASE WHEN report_status = 'Forward to captain.' THEN 1 ELSE 0 END) AS fwrdcaptain_count,
        SUM(CASE WHEN report_status = 'Forward to cityhall.' THEN 1 ELSE 0 END) AS fwrdcityhall_count,
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