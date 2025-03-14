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

$group->get('/fetch/{businessId}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT businessId, businessName, streetBuildingHouse, barangay, municipality FROM businessinformation WHERE businessId = :businessId";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':businessId', $args['businessId'], PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
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

$group->get('/inspectionSchedule', function (Request $request, Response $response) use ($db) {
    $query = "SELECT inspectionDate FROM inspectionschedule";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/schedule', function (Request $request, Response $response) use ($db) {
    try {
    $input = $request->getParsedBody();

    // Start the transaction
    $db->beginTransaction();

    //fetch inspection
    $schedulenumberquery = "SELECT COUNT(inspectionDate) AS inspectionSchedule FROM inspectionschedule WHERE inspectionDate = :inspectionDate";
    $schedulenumberstmt = $db->prepare($schedulenumberquery);
    $schedulenumberstmt->bindParam(':inspectionDate', $input['inspectionDate']);
    $schedulenumberstmt->execute();
    $schedules = $schedulenumberstmt->fetch(PDO::FETCH_ASSOC);

    //fetch record
    $schedulerecordquery = "SELECT COUNT(businessId) AS record FROM inspectionschedule WHERE businessId = :businessId";
    $schedulerecordstmt = $db->prepare($schedulerecordquery);
    $schedulerecordstmt->bindParam(':businessId', $input['businessId']);
    $schedulerecordstmt->execute();
    $schedulerecord = $schedulerecordstmt->fetch(PDO::FETCH_ASSOC);

    if(($schedulerecord['record'] == 1) && ($schedules['inspectionSchedule'] == 5)){
        $db->rollback();
        // Error response for too many slots
        $response->getBody()->write(json_encode(['error' => 'The slot is full and already scheduled.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }else if($schedules['inspectionSchedule'] == 5){
        $db->rollback();
        // Error response for too many slots
        $response->getBody()->write(json_encode(['error' => 'The slot is full.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }else if ($schedulerecord['record'] == 1){
        $db->rollback();
        // Error response for too many slots
        $response->getBody()->write(json_encode(['error' => 'Already scheduled.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    //inspection schedule
    $schedulequery = "INSERT INTO inspectionschedule (businessId, inspectionDate, assignedDay, timeFrom, timeTo, assignedInspectors)
                                      VALUES (:businessId, :inspectionDate, :assignedDay, :timeFrom, :timeTo, :assignedInspectors)";
    $schedulestmt = $db->prepare($schedulequery);
    $schedulestmt->bindParam(':businessId', $input['businessId']);
    $schedulestmt->bindParam(':inspectionDate', $input['inspectionDate']);
    $schedulestmt->bindParam(':assignedDay', $input['assignedDay']);
    $schedulestmt->bindParam(':timeFrom', $input['timeFrom']);
    $schedulestmt->bindParam(':timeTo', $input['timeTo']);
    $schedulestmt->bindParam(':assignedInspectors', $input['assignedInspectors']);
    $schedulestmt->execute();

    //business status
    $statusquery = "UPDATE businessstatus SET businessStatus = :businessStatus, statusReason = :statusReason WHERE businessId = :businessId";
    $statusstmt = $db->prepare($statusquery);
    $businessId = $input['businessId'];
    $businessStatus = "Scheduled.";
    $statusReason = "The business are scheduled for inspection.";
    $statusstmt->bindParam(':businessId', $businessId);
    $statusstmt->bindParam(':businessStatus', $businessStatus);
    $statusstmt->bindParam(':statusReason', $statusReason);
    $statusstmt->execute();

    // Commit the transaction to make the changes permanent
    $db->commit();

    } catch (PDOException $e) {
        //If there’s an error, roll back the transaction
    $db->rollBack();
    
    error_log( "Error: " . $e->getMessage());
    
    }

    $response->getBody()->write(json_encode('Schedule successfully.'));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/inspectors/{inspectionDate}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT assignedInspectors FROM inspectionschedule WHERE inspectionDate = :inspectionDate";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':inspectionDate', $args['inspectionDate'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/inspector/{assignedDay}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT inspectorName FROM `inspectorsinformation` WHERE assignedDay LIKE :assignedDay";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':assignedDay', '%' . $args['assignedDay'] . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/scheduleValidation/{timeFrom}/{timeTo}/{inspectionDate}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT COUNT(businessId) AS businessCount FROM inspectionschedule WHERE timeFrom < :timeTo AND timeTo > :timeFrom AND inspectionDate = :inspectionDate";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':timeFrom', $args['timeFrom'], PDO::PARAM_STR);
    $stmt->bindValue(':timeTo', $args['timeTo'], PDO::PARAM_STR);
    $stmt->bindValue(':inspectionDate', $args['inspectionDate'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

