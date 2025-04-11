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

