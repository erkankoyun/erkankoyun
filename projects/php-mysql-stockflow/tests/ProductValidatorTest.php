<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use StockFlow\Validation\ProductValidator;

final class ProductValidatorTest extends TestCase
{
    public function testValidProductPassesValidation(): void
    {
        self::assertSame([], ProductValidator::validate(['sku' => 'KB-100', 'name' => 'Mechanical Keyboard', 'description' => 'Hot-swappable keyboard.', 'price' => 89.99, 'quantity' => 12, 'status' => 'active']));
    }

    public function testRequiredFieldsAreValidated(): void
    {
        $errors = ProductValidator::validate([]);
        self::assertArrayHasKey('sku', $errors);
        self::assertArrayHasKey('name', $errors);
        self::assertArrayHasKey('price', $errors);
        self::assertArrayHasKey('quantity', $errors);
        self::assertArrayHasKey('status', $errors);
    }

    public function testNegativeValuesAreRejected(): void
    {
        $errors = ProductValidator::validate(['sku' => 'BAD-1', 'name' => 'Invalid', 'price' => -1, 'quantity' => -5, 'status' => 'active']);
        self::assertArrayHasKey('price', $errors);
        self::assertArrayHasKey('quantity', $errors);
    }

    public function testPartialUpdateOnlyValidatesProvidedFields(): void
    {
        self::assertSame([], ProductValidator::validate(['quantity' => 4], true));
    }
}
