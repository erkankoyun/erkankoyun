<?php

declare(strict_types=1);

namespace StockFlow\Http;

final class JsonResponse
{
    public static function send(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }

    public static function error(string $message, int $status, array $details = []): never
    {
        $payload = ['error' => ['message' => $message]];
        if ($details !== []) {
            $payload['error']['details'] = $details;
        }
        self::send($payload, $status);
    }
}
