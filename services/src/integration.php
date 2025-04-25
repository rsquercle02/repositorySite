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