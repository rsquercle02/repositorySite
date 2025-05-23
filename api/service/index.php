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
    "methods" => ["GET", "POST", "PUT"],
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

// Reports Service Group with Rate Limiting
$app->group('/reports', function (RouteCollectorProxy $group) {
    
    require __DIR__ . '/../../services/src/reports.php';
})->add(rateLimitMiddleware('reports-service', 55, 60)); // 55 requests per 60 seconds

// Integration Service Group with Rate Limiting
$app->group('/integration', function (RouteCollectorProxy $group) {
    
    require __DIR__ . '/../../services/src/integration.php';
})->add(rateLimitMiddleware('integration-service', 55, 60)); // 55 requests per 60 seconds

// AI Service Group with Rate Limiting
$app->group('/ai', function (RouteCollectorProxy $group) {
    
    require __DIR__ . '/../../services/src/ai.php';
})->add(rateLimitMiddleware('ai-service', 55, 60)); // 55 requests per 60 seconds

$app->get('/sessiontest', function (Request $request, Response $response) {
    
    //$_SESSION['id'] = '15';
    //$_SESSION['username'] = 'jonard';
    if (isset($_SESSION['id'])) {
        $responseData = [
            'id' => $_SESSION['id'],
            'username' => $_SESSION['username']
        ];
    } else {
        $responseData = [
            'message' => "Session 'id' not found."
        ];
    }

    $response->getBody()->write(json_encode($responseData));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->run();
