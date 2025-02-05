<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->setBasePath('/api-gateway/public');

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

// User Service Group with Rate Limiting
$app->group('/inspection', function (RouteCollectorProxy $group) {
    
    require __DIR__ . '/../../inspection/src/routes.php';
})->add(rateLimitMiddleware('user-service', 55, 60)); // 5 requests per 60 seconds

// Product Service Group with Rate Limiting
$app->group('/product-service', function (RouteCollectorProxy $group) {

})->add(rateLimitMiddleware('product-service', 10, 120)); // 10 requests per 120 seconds

$app->run();
