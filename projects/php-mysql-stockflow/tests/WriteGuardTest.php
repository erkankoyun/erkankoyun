<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use StockFlow\Security\WriteGuard;

final class WriteGuardTest extends TestCase
{
    public function testMatchingKeyIsAuthorized(): void
    {
        self::assertTrue(WriteGuard::isAuthorized('portfolio-key', 'portfolio-key'));
    }

    public function testWrongOrMissingKeyIsRejected(): void
    {
        self::assertFalse(WriteGuard::isAuthorized(null, 'portfolio-key'));
        self::assertFalse(WriteGuard::isAuthorized('wrong', 'portfolio-key'));
        self::assertFalse(WriteGuard::isAuthorized('anything', ''));
    }
}
