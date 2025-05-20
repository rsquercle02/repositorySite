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

$group->get('/business', function (Request $request, Response $response) use ($db) {
    $url = "https://businesspermit.unifiedlgu.com/admin/business_approve_data_list_table.php";
    
    // Fetch the external data
    $data = file_get_contents($url);
    
    // Write it to the response body directly (assuming it's already JSON)
    $response->getBody()->write($data);
    
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/census', function (Request $request, Response $response) use ($db) {
    $url = "https://backend-api-5m5k.onrender.com/api/cencus";
    
    // Fetch the external data
    $data = file_get_contents($url);
    
    // Write it to the response body directly (assuming it's already JSON)
    $response->getBody()->write($data);
    
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/registeredStoreViolations', function (Request $request, Response $response) use ($db) {
    // Fetch data from APIs
    $stores_json = file_get_contents("https://businesspermit.unifiedlgu.com/admin/business_approve_data_list_table.php");
    // $violations_json = file_get_contents("http://localhost:8001/api/service/concernslist/storeissues");

    // Decode JSON into PHP associative arrays
    $stores = json_decode($stores_json, true);
    // $violations = json_decode($violations_json, true);

    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    // Fetch violations from the database dynamically
    $query = "SELECT DISTINCT s.store_id, vt.vtrail_id, vt.sviolation_id, vt.store_violation, vt.date_stamp
            FROM violationstrail vt
            JOIN storeviolations sv ON vt.sviolation_id = sv.sviolation_id
            JOIN stores s ON s.store_id = sv.store_id
            WHERE s.record_status = 'has records.' AND vt.store_violation != 'None' AND vt.date_stamp LIKE :searchDate";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchDate', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Fetch violations from the database dynamically
            $query = "SELECT DISTINCT s.store_id, vt.vtrail_id, vt.sviolation_id, vt.store_violation, vt.date_stamp
            FROM violationstrail vt
            JOIN storeviolations sv ON vt.sviolation_id = sv.sviolation_id
            JOIN stores s ON s.store_id = sv.store_id
            WHERE s.record_status = 'has records.' AND vt.store_violation != 'None'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Group violations by store_id
    $violations_by_store = [];
    foreach ($violations as $v) {
        $store_id = $v['store_id'];
        if (!isset($violations_by_store[$store_id])) {
            $violations_by_store[$store_id] = [];
        }
        // Split violations into individual violation types
        $individual_violations = explode(',', $v['store_violation']); // Split by comma if multiple violations are listed
        foreach ($individual_violations as $violation) {
            $violation = trim($violation); // Trim any extra spaces
            $violations_by_store[$store_id][] = $violation;
        }
    }

    // Function to count violations per type
    function countViolations($violations) {
        $violation_counts = [];
        foreach ($violations as $violation) {
            // Format violation name to lowercase
            $formatted_violation = strtolower($violation);

            // Increment the count for this violation type
            if (!isset($violation_counts[$formatted_violation])) {
                $violation_counts[$formatted_violation] = 0;
            }
            $violation_counts[$formatted_violation]++;
        }
        return $violation_counts;
    }

    // Final result array
    $store_violation_summary = [];

    // Loop through stores and compile their violation counts
    foreach ($stores as $store) {
        $store_id = $store['id'];
        $violation_counts = isset($violations_by_store[$store_id]) 
            ? countViolations($violations_by_store[$store_id])
            : [];

        // Format the violation counts to ensure they appear as expected
        $formatted_counts = [];
        foreach ($violation_counts as $violation => $count) {
            // Replace underscores with spaces and ensure correct capitalization
            $formatted_violation = ucwords(str_replace('_', ' ', $violation));
            $formatted_counts[$formatted_violation] = $count;
        }

        // Add store summary with violations
        $store_violation_summary[] = [
            'store_id' => $store_id,
            'business_name' => $store['business_name'],
            'business_address' => $store['business_address'],
            'violation_counts' => $formatted_counts
        ];
    }

    // Sort stores by total violations descending
    usort($store_violation_summary, function($a, $b) {
        $a_total = array_sum($a['violation_counts']);
        $b_total = array_sum($b['violation_counts']);
        return $b_total - $a_total;
    });

    // Output result as JSON response
    $payload = json_encode($store_violation_summary, JSON_PRETTY_PRINT);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/notRegisteredStoreViolations', function (Request $request, Response $response) use ($db) {
    // Fetch violations from the database dynamically
    $query = "SELECT DISTINCT s.store_id AS id, s.store_name AS business_name, s.store_address AS business_address
            FROM stores s
            WHERE s.record_status = 'no records.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retrieve all query parameters as an array
    $queryParams = $request->getQueryParams();

    // Get the specific 'search' query parameter
    $search = isset($queryParams['search']) ? $queryParams['search'] : null;

    if($search != null){
    // Fetch violations from the database dynamically
    $query = "SELECT DISTINCT s.store_id, vt.vtrail_id, vt.sviolation_id, vt.store_violation, vt.date_stamp
            FROM violationstrail vt
            JOIN storeviolations sv ON vt.sviolation_id = sv.sviolation_id
            JOIN stores s ON s.store_id = sv.store_id
            WHERE s.record_status = 'no records.' AND vt.store_violation != 'None' AND vt.date_stamp LIKE :searchDate";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchDate', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else{
        // Fetch violations from the database dynamically
            $query = "SELECT DISTINCT s.store_id, vt.vtrail_id, vt.sviolation_id, vt.store_violation, vt.date_stamp
            FROM violationstrail vt
            JOIN storeviolations sv ON vt.sviolation_id = sv.sviolation_id
            JOIN stores s ON s.store_id = sv.store_id
            WHERE s.record_status = 'no records.' AND vt.store_violation != 'None'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Group violations by store_id
    $violations_by_store = [];
    foreach ($violations as $v) {
        $store_id = $v['store_id'];
        if (!isset($violations_by_store[$store_id])) {
            $violations_by_store[$store_id] = [];
        }
        // Split violations into individual violation types
        $individual_violations = explode(',', $v['store_violation']); // Split by comma if multiple violations are listed
        foreach ($individual_violations as $violation) {
            $violation = trim($violation); // Trim any extra spaces
            $violations_by_store[$store_id][] = $violation;
        }
    }

    // Function to count violations per type
    function countViolations($violations) {
        $violation_counts = [];
        foreach ($violations as $violation) {
            // Format violation name to lowercase
            $formatted_violation = strtolower($violation);

            // Increment the count for this violation type
            if (!isset($violation_counts[$formatted_violation])) {
                $violation_counts[$formatted_violation] = 0;
            }
            $violation_counts[$formatted_violation]++;
        }
        return $violation_counts;
    }

    // Final result array
    $store_violation_summary = [];

    // Loop through stores and compile their violation counts
    foreach ($stores as $store) {
        $store_id = $store['id'];
        $violation_counts = isset($violations_by_store[$store_id]) 
            ? countViolations($violations_by_store[$store_id])
            : [];

        // Format the violation counts to ensure they appear as expected
        $formatted_counts = [];
        foreach ($violation_counts as $violation => $count) {
            // Replace underscores with spaces and ensure correct capitalization
            $formatted_violation = ucwords(str_replace('_', ' ', $violation));
            $formatted_counts[$formatted_violation] = $count;
        }

        // Add store summary with violations
        $store_violation_summary[] = [
            'store_id' => $store_id,
            'business_name' => $store['business_name'],
            'business_address' => $store['business_address'],
            'violation_counts' => $formatted_counts
        ];
    }

    // Sort stores by total violations descending
    usort($store_violation_summary, function($a, $b) {
        $a_total = array_sum($a['violation_counts']);
        $b_total = array_sum($b['violation_counts']);
        return $b_total - $a_total;
    });

    // Output result as JSON response
    $payload = json_encode($store_violation_summary, JSON_PRETTY_PRINT);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$group->get('/InfoTally', function (Request $request, Response $response) use ($db) {
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

    $violationcounts_wrapped = ["0" => $violationcounts];

    $query = "SELECT COUNT(store_id) AS store_count FROM `stores`";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $storedata = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(report_status) AS report_resolved FROM rstatus WHERE report_status = 'Report resolved.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $resolvedreport = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(report_status) AS report_created FROM rstatus WHERE report_status = 'Report created.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reportcreated = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $query = "SELECT 
        SUM(CASE WHEN report_status = 'Report created.' THEN 1 ELSE 0 END) AS reportcreated_count,
        SUM(CASE WHEN report_status = 'Report resolved.' THEN 1 ELSE 0 END) AS reportresolved_count,
        COUNT(report_id) AS report_total
        FROM rstatus
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reporttally = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT COUNT(DISTINCT DATE_FORMAT(operation_date, '%Y-%m')) AS clrngops_total
        FROM clearingoperations
        WHERE status = 'Sent.'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $clrngopstally = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT 
        SUM(CASE WHEN report_category = 'Low risk.' THEN 1 ELSE 0 END) AS lowrisk__count,
        SUM(CASE WHEN report_category = 'Medium risk.' THEN 1 ELSE 0 END) AS mediumrisk_count,
        SUM(CASE WHEN report_category = 'High risk.' THEN 1 ELSE 0 END) AS highrisk_count
        FROM reports
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $reportcategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT DISTINCT COUNT(concern_id) AS total_concerns FROM concerns";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $totalConcerns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine both results into a single associative array
    $responseData = array_merge($violationcounts_wrapped, $storedata, $resolvedreport, $reportcreated, $reporttally, $clrngopstally, $reportcategory, $totalConcerns);


    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});