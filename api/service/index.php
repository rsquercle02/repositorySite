<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Tuupola\Middleware\CorsMiddleware;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->setBasePath('/api/service');
$app->add(new Tuupola\Middleware\CorsMiddleware([
    "origin" => ["*"],  // ✅ allow all origins
    "methods" => ["GET", "POST"],
    "headers.allow" => ["Content-Type", "Authorization"],
    "headers.expose" => ["Authorization"],
    "credentials" => true, // ✅ allows cookies to be sent
    "cache" => 0,
]));




// Initialize session for rate limiting (for demonstration purposes)
if (!isset($_SESSION)) {
    session_start();
}


// Rate Limiting Middleware
function rateLimitMiddleware($serviceKey, $limit, $timeFrame) {
    return function (Request $request, $handler) use ($serviceKey, $limit, $timeFrame) {
        $clientIp = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitKey = "{$serviceKey}_{$clientIp}";

        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = ['count' => 0, 'start_time' => time()];
        }

        $rateData = &$_SESSION[$rateLimitKey];
        $currentTime = time();

        if ($currentTime - $rateData['start_time'] > $timeFrame) {
            // Reset the rate limiting data
            $rateData['count'] = 0;
            $rateData['start_time'] = $currentTime;
        }

        $rateData['count']++;

        if ($rateData['count'] > $limit) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => "Rate limit exceeded. Try again in " . ($timeFrame - ($currentTime - $rateData['start_time'])) . " seconds."
            ]));
            return $response->withStatus(429)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    };
}

// Verification Service Group with Rate Limiting
$app->group('/verification', function (RouteCollectorProxy $group) {
    
    require __DIR__ . '/../../services/src/verification.php';
})->add(rateLimitMiddleware('verification-service', 55, 60)); // 55 requests per 60 seconds

// Schedule Service Group with Rate Limiting
$app->group('/schedule', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/schedule.php';
})->add(rateLimitMiddleware('inspection-service', 55, 60)); // 55 requests per 60 seconds

// Approval Service Group with Rate Limiting
$app->group('/approval', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/approval.php';
})->add(rateLimitMiddleware('approval-service', 55, 60)); // 55 requests per 60 seconds

// Inspection Service Group with Rate Limiting
$app->group('/inspection', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/inspection.php';
})->add(rateLimitMiddleware('inspection-service', 55, 60)); // 55 requests per 60 seconds

// Registration Service Group with Rate Limiting
$app->group('/registration', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/registration.php';
})->add(rateLimitMiddleware('registration-service', 55, 60)); // 55 requests per 60 seconds

// User Management Service Group with Rate Limiting
$app->group('/usermanagement', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/usermanagement.php';
})->add(rateLimitMiddleware('usermanagement-service', 55, 60)); // 55 requests per 60 seconds

// Safety Monitoring Service Group with Rate Limiting
$app->group('/safetymonitoring', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/safetymonitoring.php';
})->add(rateLimitMiddleware('safetymonitoring-service', 55, 60)); // 55 requests per 60 seconds

// concerns Service Group with Rate Limiting
$app->group('/concerns', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/concerns.php';
})->add(rateLimitMiddleware('concerns-service', 55, 60)); // 55 requests per 60 seconds

// Concerns List Service Group with Rate Limiting
$app->group('/concernslist', function (RouteCollectorProxy $group) {

    require __DIR__ . '/../../services/src/concernslist.php';
})->add(rateLimitMiddleware('concernslist-service', 55, 60)); // 55 requests per 60 seconds

// Report List Service Group with Rate Limiting
$app->group('/reportlist', function (RouteCollectorProxy $group) {
    
    require __DIR__ . '/../../services/src/reportlist.php';
})->add(rateLimitMiddleware('report-service', 55, 60)); // 55 requests per 60 seconds

$app->get('/sessiontest', function (Request $request, Response $response) {
    // Setting the session variable somewhere (e.g., after login)
    //$_SESSION["id"] = '115';
    // Check if the session value exists
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];  // Get session value
        $username = $_SESSION['username'];  // Get session value
        $profile = $_SESSION['profile'];  // Get session value
        $barangayRole = $_SESSION['barangayRole'];  // Get session value
        $status = $_SESSION['status'];  // Get session value
        $picture = $_SESSION['picture'];  // Get session value
    } else {
        $data = "Session 'id' not found.";  // If not found
    }
    
    // Combine both results into a single associative array
    $responseData = $id . $username . $profile . $barangayRole . $status . $picture;
    //$data = $_SESSION["id"];
    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
