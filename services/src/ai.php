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

// Handle the preflight (OPTIONS) request
$group->options('/generatecontent', function ($request, $response, $args) {
    return $response->withHeader('Content-Type', 'application/json')
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

//AI summary and suggestions
// Define the route to generate content using Gemini API
$group->get('/generatecontent', function (Request $request, Response $response, $args) use ($db) {
    // Get the data (e.g., prompt) from the incoming JSON request body
    $data = $request->getParsedBody();
    //$prompt = $data['prompt'];  // Default prompt if none provided

    // API key (replace with your actual API key)
    $apiKey = $_ENV['API_KEY']; // or $_ENV['API_KEY']; 

    // Initialize Guzzle client
    $client = new Client();

    $query = "SELECT DISTINCT s.store_id, vt.vtrail_id, vt.sviolation_id, vt.store_violation, vt.date_stamp
            FROM violationstrail vt
            JOIN storeviolations sv ON vt.sviolation_id = sv.sviolation_id
            JOIN stores s ON s.store_id = sv.store_id
            WHERE vt.store_violation != 'None'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $violationlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize violation count array
    $violationcounts = [];

    // Loop through each record
    foreach ($violationlist as $record) {
        // Split store_violation by comma and trim whitespace
        $violations = array_map('trim', explode(',', $record['store_violation']));

        // Count each violation occurrence
        foreach ($violations as $violation) {
            if (!isset($violationcounts[$violation])) {
                $violationcounts[$violation] = 0;
            }
            $violationcounts[$violation]++;
        }
    }

    $violationdata = ["0" => $violationcounts];

    $query = "SELECT COUNT(store_id) AS store_count FROM `stores`";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $storedata = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT DISTINCT
    SUM(CASE WHEN report_category = 'Low risk.' THEN 1 ELSE 0 END) AS lowrisk_report,
    SUM(CASE WHEN report_category = 'Medium risk.' THEN 1 ELSE 0 END) AS mediumrisk_report,
    SUM(CASE WHEN report_category = 'High risk.' THEN 1 ELSE 0 END) AS highrisk_report
    FROM reports
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reportcategory = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $query = "SELECT DISTINCT
        SUM(CASE WHEN report_status = 'Report created.' THEN 1 ELSE 0 END) AS reportcreated_count,
        SUM(CASE WHEN report_status = 'Report resolved.' THEN 1 ELSE 0 END) AS reportresolved_count
    FROM rstatus
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reportstatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT DISTINCT COUNT(clr.clrngops_id) AS clrngops_count FROM clrngopsrprt clr 
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Sent.' ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clrngopstally = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine both results into a single associative array
    $responseData = array_merge($violationdata, $storedata, $reportcategory, $reportstatus, $clrngopstally);

    try{

    // Gemini API endpoint URL
    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';


    $prompt = 'Can you generate insights from the data?' . "\n" .
              'Store violations data:' . json_encode($responseData[0], JSON_PRETTY_PRINT) . "\n" .
              'Number of stores reported:' . json_encode($responseData[1], JSON_PRETTY_PRINT) . "\n" .
              'Reports category:' . json_encode($responseData[2], JSON_PRETTY_PRINT) . "\n" .
              'Reports status:' . json_encode($responseData[3], JSON_PRETTY_PRINT) . "\n" .
              'Reported clearing operations:' . json_encode($responseData[4], JSON_PRETTY_PRINT) . "\n\n" .
              'Can you create a narrative report based on the data provided?' . "\n" .
              'Can you find the top three store issues with details and impacts to the community?' . "\n" .
              'Can you give updates about the categories of reports?' . "\n" .
              'Can you give updates about the status of reports?' . "\n" .
              'Can you provide actions, events and seminars that the barangay can do to lessen the reports and issues of stores?' . "\n" .
              'Can you group the response based on the questions and make the response be professional reporting also don\'t include date on the report?';
    
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

$group->post('/generatereminders', function (Request $request, Response $response, $args) use ($db) {
    // Get the data (e.g., prompt) from the incoming JSON request body
    $data = $request->getParsedBody();
    $prompttext = $data['prompt'];
    $reportlist = $data['report_list'];
    $datetoday = $data['date_today'];

    // API key (replace with your actual API key)
    $apiKey = $_ENV['API_KEY1']; // or $_ENV['API_KEY']; 

    // Initialize Guzzle client
    $client = new Client();

    try{

    // Gemini API endpoint URL
    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';

    // Concatenate the variables directly into the $prompt string without extra text
    $prompt = $prompttext . "\n" .
            json_encode($reportlist, JSON_PRETTY_PRINT) . "\n" .
            $datetoday;

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

