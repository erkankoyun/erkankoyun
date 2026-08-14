<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use StockFlow\Support\Pagination;

final class PaginationTest extends TestCase
{
    public function testDefaultsAreApplied(): void
    {
        self::assertSame(['page' => 1, 'per_page' => 10, 'offset' => 0], Pagination::fromQuery([]));
    }

    public function testOffsetIsCalculated(): void
    {
        self::assertSame(['page' => 3, 'per_page' => 25, 'offset' => 50], Pagination::fromQuery(['page' => '3', 'per_page' => '25']));
    }
}
