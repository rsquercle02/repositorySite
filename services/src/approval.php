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

    $query = "SELECT COUNT(report_status) AS resolved_report FROM rstatus WHERE report_status = 'Resolved.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resolvedreport = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(report_status) AS fwrdcity_report FROM rstatus WHERE report_status = 'Forwarded to cityhall.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $frwdcityreport = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT 
        SUM(CASE WHEN report_status = 'For kagawad.' THEN 1 ELSE 0 END) AS forkagawad_count,
        SUM(CASE WHEN report_status = 'For captain.' THEN 1 ELSE 0 END) AS forcaptain_count,
        SUM(CASE WHEN report_status = 'Forwarded to cityhall.' THEN 1 ELSE 0 END) AS fwrdcityhall_count,
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
    
    
    /*
    try {
        $Url1 = 'http://localhost:8001/api/service/concernslist/InfoTally';
        $Response1 = $client->request('GET', $Url1);
        $Data1 = json_decode($Response1->getBody(), true);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        echo "HTTP Request failed: " . $e->getMessage();
    } */

    try{

    // Fetching data from the database
    //$Url1 = 'http://localhost:8001/api/service/concernslist/InfoTally';
    //$Response1 = $client->request('GET', $Url1);

    // Process the first external response
    //$Data1 = json_decode($Response1->getBody(), true); // Decode the JSON response into an associative array

    // Option 1: Dump the whole array to inspect
    //print_r($Data1); // or use var_dump($Data1) if you want type info too

    // Option 2: Encode back to JSON for viewing or logging
    //echo json_encode($Data1, JSON_PRETTY_PRINT);

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

