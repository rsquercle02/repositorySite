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
    $stmt->bindValue(':staff', $_SESSION['barangayRole']);
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
    cst.concern_status,
    cst.cstatus_reason,
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

        //business status
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
        $response->getBody()->write(json_encode(['message' => 'User created successfully.']));
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
