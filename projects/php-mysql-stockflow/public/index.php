<?php

declare(strict_types=1);

use JsonException;
use StockFlow\Config\Environment;
use StockFlow\Controllers\ProductController;
use StockFlow\Database;
use StockFlow\Http\JsonResponse;
use StockFlow\Repositories\ProductRepository;
use StockFlow\Security\WriteGuard;
use Throwable;

require dirname(__DIR__).'/vendor/autoload.php';
Environment::load(dirname(__DIR__).'/.env');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

function jsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    try {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        JsonResponse::error('Request body must contain valid JSON.', 400);
    }
    if (! is_array($decoded)) {
        JsonResponse::error('JSON request body must be an object.', 400);
    }
    return $decoded;
}

function requireWriteAccess(): void
{
    $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
    if (! WriteGuard::isAuthorized(is_string($providedKey) ? $providedKey : null)) {
        JsonResponse::error('A valid X-API-Key header is required for this operation.', 401);
    }
}

try {
    if ($path === '/health' && $method === 'GET') {
        $pdo = Database::connect();
        $pdo->query('SELECT 1');
        JsonResponse::send(['status' => 'ok', 'service' => 'stockflow-api', 'database' => 'connected', 'timestamp' => gmdate(DATE_ATOM)]);
    }

    $controller = new ProductController(new ProductRepository(Database::connect()));
    if ($path === '/api/products') {
        match ($method) {
            'GET' => $controller->index($_GET),
            'POST' => (function () use ($controller): never { requireWriteAccess(); $controller->store(jsonBody()); })(),
            default => JsonResponse::error('Method not allowed.', 405),
        };
    }

    if (preg_match('#^/api/products/(\d+)$#', $path, $matches) === 1) {
        $id = (int) $matches[1];
        match ($method) {
            'GET' => $controller->show($id),
            'PUT', 'PATCH' => (function () use ($controller, $id): never { requireWriteAccess(); $controller->update($id, jsonBody()); })(),
            'DELETE' => (function () use ($controller, $id): never { requireWriteAccess(); $controller->destroy($id); })(),
            default => JsonResponse::error('Method not allowed.', 405),
        };
    }

    JsonResponse::error('Route not found.', 404);
} catch (Throwable $exception) {
    error_log($exception->__toString());
    $debug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOL);
    JsonResponse::error('Internal server error.', 500, $debug ? ['exception' => $exception->getMessage()] : []);
}
