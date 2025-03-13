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
$db = (new Database1())->getConnection();

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../data');
$dotenv->load();

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

$group->get('/inspector/{assignedDay}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT inspectorName FROM `inspectorsinformation` WHERE assignedDay LIKE :assignedDay";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':assignedDay', '%' . $args['assignedDay'] . '%', PDO::PARAM_STR);
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

$group->get('/inspectionSchedule', function (Request $request, Response $response) use ($db) {
    $query = "SELECT inspectionDate FROM inspectionschedule";
    $stmt = $db->prepare($query);
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

$group->get('/inspectors/{inspectionDate}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT assignedInspectors FROM inspectionschedule WHERE inspectionDate = :inspectionDate";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':inspectionDate', $args['inspectionDate'], PDO::PARAM_STR);
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

$group->get('/fetchstatus/{id}', function (Request $request, Response $response, $args) use ($db) {
    //fetch business status
    $businessstatussquery = "SELECT businessStatus FROM businessstatus WHERE businessId = :business_id";
    $businessstatusstmt = $db->prepare($businessstatussquery);
    $businessstatusstmt->bindParam(':business_id', $args['id'], PDO::PARAM_INT);
    $businessstatusstmt->execute();
    $status = $businessstatusstmt->fetch(PDO::FETCH_ASSOC);

    if($status === "Inspected."){
        $db->rollback();
        // Error response for business already inspected.
        $response->getBody()->write(json_encode(['error' => 'Already inspected.']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode(['data' => 'Not inspected.']));
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

$group->get('/searchapproval/{searchTerm}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    i.inspection_date
    FROM
        businessinformation bi
    JOIN inspections i ON bi.businessId = i.business_id
    JOIN businessstatus bs ON i.business_id = bs.businessId
    WHERE
        bs.businessstatus = 'Inspected.' AND bi.businessName LIKE :searchTerm";

    $stmt = $db->prepare($query);
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

$group->get('/fetchinspectionresults/{id}/{inspectiondate}', function (Request $request, Response $response, $args) use ($db) {
    $query = "SELECT
    i.inspection_date, i.inspector_name, c.category AS category, q.question, r.response
    FROM inspections i
    JOIN inspection_responses r ON i.id = r.inspection_id
    JOIN inspection_criteria q ON r.criteria_id = q.criteria_id
    JOIN inspection_categories c ON q.category_id = c.id
    WHERE i.business_id = :id AND i.inspection_date = :inspectiondate";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $args['id'], PDO::PARAM_INT);
    $stmt->bindParam(':inspectiondate', $args['inspectiondate'], PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/approval', function (Request $request, Response $response) use ($db) {
    $query = "SELECT
    bi.businessId,
    bi.businessName,
    i.inspection_date
    FROM
        businessinformation bi
    JOIN inspections i ON bi.businessId = i.business_id
    JOIN businessstatus bs ON i.business_id = bs.businessId
    WHERE
        bs.businessstatus = 'Inspected.'";

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

$group->post('/inspectionform', function (Request $request, Response $response) use ($db) {
    try {
    $input = $request->getParsedBody();

    // Start the transaction
    $db->beginTransaction();

    //post inspection details
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

//AI summary and suggestions
// Define the route to generate content using Gemini API
$group->post('/generatecontent', function (Request $request, Response $response, $args) {
    // Get the data (e.g., prompt) from the incoming JSON request body
    $data = $request->getParsedBody();
    $prompt = $data['prompt'];  // Default prompt if none provided

    // API key (replace with your actual API key)
    $apiKey = $_ENV['API_KEY']; // or $_ENV['API_KEY']; 

    // Initialize Guzzle client
    $client = new Client();

    // Gemini API endpoint URL
    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent';

    // Request data to be sent to the Gemini API
    $requestData = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    try {
        // Send a POST request to the Gemini API
        $apiResponse = $client->post($url, [
            'query' => ['key' => $apiKey],  // API key as a query parameter
            'json' => $requestData,         // Send JSON data in the body of the request
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        // Get the API response body
        $responseBody = $apiResponse->getBody()->getContents();

        // Decode the response body into an array
        $responseData = json_decode($responseBody, true);

        // Return the response as JSON
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Catch any request exceptions and return the error message
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});