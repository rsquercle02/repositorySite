<?php
//session_start();

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

$group->get('/fetchschedule', function (Request $request, Response $response) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    CONCAT(bi.streetBuildingHouse, ' ', bi.barangay, ', ', bi.municipality) AS Location,
    se.inspectionDate,
    se.assignedDay,
    se.assignedInspectors
    FROM
        inspectionschedule se
    JOIN businessinformation bi ON se.businessId = bi.businessId
    JOIN businessstatus bs ON bi.businessId = bs.businessId
    WHERE
        bs.businessstatus = 'Scheduled.' AND se.assignedInspectors LIKE :username ORDER BY se.inspectionDate ASC";
    $stmt = $db->prepare($query);
    $username = $_SESSION["username"];
    $stmt->bindValue(':username', '%' . $username . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/searchinspection/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    CONCAT(bi.streetBuildingHouse, ' ', bi.barangay, ', ', bi.municipality) AS Location,
    se.inspectionDate,
    se.assignedDay,
    se.assignedInspectors
    FROM
        inspectionschedule se
    JOIN businessinformation bi ON se.businessId = bi.businessId
    JOIN businessstatus bs ON bi.businessId = bs.businessId
    WHERE
        bs.businessstatus = 'Scheduled.' AND se.assignedInspectors LIKE :username AND bi.businessName LIKE :searchTerm ORDER BY se.inspectionDate ASC";

    $stmt = $db->prepare($query);
    $username = $_SESSION["username"];
    $stmt->bindValue(':username', '%' . $username . '%', PDO::PARAM_STR);
    $searchTerm = $args['searchTerm'];
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchinspectiondetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName, bi.businessType,
    CONCAT(bi.streetBuildingHouse, ' ', bi.barangay, ', ', bi.municipality) AS Location, CONCAT(se.inspectionDate, ', ', se.assignedDay) AS inspectionDate,
    se.assignedInspectors
    FROM
        inspectionschedule se
    JOIN businessinformation bi ON se.businessId = bi.businessId
    JOIN businessstatus bs ON bi.businessId = bs.businessId
    WHERE
        bi.businessId = :id AND bs.businessstatus = 'Scheduled.' AND se.assignedInspectors LIKE :username";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $username = $_SESSION["username"];
    $stmt->bindValue(':username', '%' . $username . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/forminspectiondetails/{id}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName, bi.businessType,
    CONCAT(bi.streetBuildingHouse, ' ', bi.barangay, ', ', bi.municipality) AS Location, CONCAT(se.inspectionDate, ', ', se.assignedDay) AS inspectionDate,
    se.assignedInspectors
    FROM
        inspectionschedule se
    JOIN businessinformation bi ON se.businessId = bi.businessId
    JOIN businessstatus bs ON bi.businessId = bs.businessId
    WHERE
        bi.businessId = :id AND bs.businessstatus = 'Scheduled.' AND se.assignedInspectors LIKE :username";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $username = $_SESSION["username"];
    $stmt->bindValue(':username', '%' . $username . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/fetchcriteria', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT c.category, q.criteria_id, q.question, q.inputType 
              FROM inspection_criteria q 
              JOIN inspection_categories c ON q.category_id = c.id 
              ORDER BY c.category";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/inspectioninfo', function (Request $request, Response $response) use ($db) {
    try {
    $input = $request->getParsedBody();

    // Start the transaction
    $db->beginTransaction();

    //fetch business status
    $businessstatussquery = "SELECT businessStatus FROM businessstatus WHERE businessId = :business_id";
    $businessstatusstmt = $db->prepare($businessstatussquery);
    $businessstatusstmt->bindParam(':business_id', $input['business_id']);
    $businessstatusstmt->execute();
    $status = $businessstatusstmt->fetch(PDO::FETCH_ASSOC);

    if($status === "Inspected."){
        $db->rollback();
        // Error response for business already inspected.
        $response->getBody()->write(json_encode(['error' => 'Already inspected.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    //post inspection details
    $inspectiondetailsquery = "INSERT INTO inspections (business_id, inspector_name, inspection_date) VALUES (:business_id, :inspector_name, :inspection_date)";
    $inspectiondetailsstmt = $db->prepare($inspectiondetailsquery);
    $business_id = $input['business_id'];
    $inspector_name = $input['inspector_name'];
    $inspection_date = $input['inspection_date'];
    $inspectiondetailsstmt->bindParam(':business_id', $business_id);
    $inspectiondetailsstmt->bindParam(':inspector_name', $inspector_name);
    $inspectiondetailsstmt->bindParam(':inspection_date', $inspection_date);
    $inspectiondetailsstmt->execute();

    // Get the last inserted inspection ID (for linking to the order)
    $inspection_id = $db->lastInsertId();

    foreach ($input as $key => $value) {
        if(is_numeric($key)){
            $criteria_id = $key;
            $response = $value;
            //post inspection responses
            $inspectionresponsesquery = "INSERT INTO inspection_responses (inspection_id, criteria_id, response) VALUES (:inspection_id, :criteria_id, :response)";
            $inspectionresponsesstmt = $db->prepare($inspectionresponsesquery);
            $inspectionresponsesstmt->bindParam(':inspection_id', $inspection_id);
            $inspectionresponsesstmt->bindParam(':criteria_id', $criteria_id);
            $inspectionresponsesstmt->bindParam(':response', $response);
            $inspectionresponsesstmt->execute();
        }
    }

    //business status
    $statusquery = "UPDATE businessstatus SET businessStatus = :businessStatus, statusReason = :statusReason WHERE businessId = :businessId";
    $statusstmt = $db->prepare($statusquery);
    $businessId = $input['business_id'];
    $businessStatus = "Inspected.";
    $statusReason = "Inspection data is submitted for approval.";
    $statusstmt->bindParam(':businessId', $businessId);
    $statusstmt->bindParam(':businessStatus', $businessStatus);
    $statusstmt->bindParam(':statusReason', $statusReason);
    $statusstmt->execute();
    
    // Commit the transaction to make the changes permanent
    $db->commit();

    } catch (PDOException $e) {
        //If theres an error, roll back the transaction
    $db->rollBack();
    
    error_log( "Error: " . $e->getMessage());
    
    }

    //$response->getBody()->write("");
    $response = new \Slim\Psr7\Response();
    $response->getBody()->write(json_encode('Schedule successfully.'));
    return $response->withHeader('Content-Type', 'application/json');
});

