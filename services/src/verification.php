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

