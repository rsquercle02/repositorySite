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
            $stmt = $db->prepare("INSERT INTO clearingoperations (operation_date, conducted, action_taken, remarks, barco_id, status) VALUES (?, ?, ?, ?, ?, ?)");
            $status = 'Pending.';
            $stmt->execute([$clearingDate, $conducted, $actionTaken, $remarks, $barcoId, $status]);
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

