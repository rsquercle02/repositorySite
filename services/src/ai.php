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
        SUM(CASE WHEN vt.store_violation LIKE '%UNSAFE FOOD HANDLING%' THEN 1 ELSE 0 END) AS unsafe_food_handling_count
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

    $query = "SELECT 
    SUM(CASE WHEN report_category = 'Low risk.' THEN 1 ELSE 0 END) AS lowrisk_report,
    SUM(CASE WHEN report_category = 'Medium risk.' THEN 1 ELSE 0 END) AS mediumrisk_report,
    SUM(CASE WHEN report_category = 'High risk.' THEN 1 ELSE 0 END) AS highrisk_report
    FROM reports
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reportcategory = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $query = "SELECT 
        SUM(CASE WHEN report_status = 'Report created.' THEN 1 ELSE 0 END) AS reportcreated_count,
        SUM(CASE WHEN report_status = 'Report resolved.' THEN 1 ELSE 0 END) AS reportresolved_count
    FROM rstatus
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reportstatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(clr.clrngops_id) AS clrngops_count FROM clrngopsrprt clr 
        JOIN clrngopsstatus cls ON clr.clrngops_id = cls.clrngops_id
        WHERE cls.clrngops_status = 'Sent.' ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clrngopstally = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine both results into a single associative array
    $responseData = array_merge($violationdata, $storedata, $clrngopstally);

    try{

    // Gemini API endpoint URL
    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent';


    $prompt = 'Can you generate insights from the data?' . 'Store issues data:' . json_encode($responseData[0], JSON_PRETTY_PRINT) . 'Number of store reported:' . json_encode($responseData[1], JSON_PRETTY_PRINT) . 'Resolved reports:' . json_encode($responseData[2], JSON_PRETTY_PRINT) . 
              'Can you create a narrative report based on the data provided?' . 
              'Can you find the top three store issues with details and impacts to the community?' . 
              'Can you provide actions that the barangay can do to lessen the reports and issues of stores?' . 
              "Can you group the response based on the questions and make the respone be professional reporting also don't include date on the report?";
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

