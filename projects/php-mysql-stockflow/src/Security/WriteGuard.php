<?php

declare(strict_types=1);

namespace StockFlow\Security;

final class WriteGuard
{
    public static function isAuthorized(?string $providedKey, ?string $configuredKey = null): bool
    {
        $configuredKey ??= getenv('API_WRITE_KEY') ?: '';
        if ($configuredKey === '' || $providedKey === null || $providedKey === '') {
            return false;
        }
        return hash_equals($configuredKey, $providedKey);
    }
}
