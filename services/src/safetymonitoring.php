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

$group->get('/fetchStore', function (Request $request, Response $response) use ($db) {
    // Retrieve the 'term' query parameter from the URL
    $queryparams = $request->getQueryParams('term');
    $searchTerm = $queryparams['term'];

    $query = "SELECT * FROM stores WHERE store_name LIKE :searchTerm";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$group->post('/violations', function (Request $request, Response $response) use ($db) {
    try {
        // Get the parsed input from the request body
        $input = $request->getParsedBody();
        //$files = $request->getUploadedFiles();

        $query = "INSERT INTO violations (store_name, store_address, violation_details) VALUES (:store_name, :store_address, :violation_details)";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':store_name', $input['storeName']);
        $stmt->bindValue(':store_address', $input['storeAddress']);
        $stmt->bindValue(':violation_details', $input['violations']);
        $stmt->execute();
        
        // Optionally, you can return a success message or result after execution
        $response->getBody()->write(json_encode(['message' => 'Violation uploaded successfully.']));
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