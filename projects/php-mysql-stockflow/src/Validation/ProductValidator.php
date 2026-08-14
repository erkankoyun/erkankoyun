<?php

declare(strict_types=1);

namespace StockFlow\Validation;

final class ProductValidator
{
    private const STATUSES = ['active', 'inactive', 'discontinued'];

    /** @return array<string,string> */
    public static function validate(array $input, bool $partial = false): array
    {
        $errors = [];
        self::requiredString($input, 'sku', 64, $partial, $errors);
        self::requiredString($input, 'name', 160, $partial, $errors);
        self::optionalString($input, 'description', 2000, $errors);

        if (! $partial || array_key_exists('price', $input)) {
            if (! isset($input['price']) || ! is_numeric($input['price']) || (float) $input['price'] < 0) {
                $errors['price'] = 'Price must be a non-negative number.';
            }
        }

        if (! $partial || array_key_exists('quantity', $input)) {
            $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($quantity === false) {
                $errors['quantity'] = 'Quantity must be a non-negative integer.';
            }
        }

        if (! $partial || array_key_exists('status', $input)) {
            $status = $input['status'] ?? null;
            if (! is_string($status) || ! in_array($status, self::STATUSES, true)) {
                $errors['status'] = 'Status must be active, inactive, or discontinued.';
            }
        }

        return $errors;
    }

    private static function requiredString(array $input, string $field, int $maxLength, bool $partial, array &$errors): void
    {
        if ($partial && ! array_key_exists($field, $input)) {
            return;
        }
        $value = $input[$field] ?? null;
        if (! is_string($value) || trim($value) === '') {
            $errors[$field] = ucfirst($field).' is required.';
            return;
        }
        if (strlen(trim($value)) > $maxLength) {
            $errors[$field] = ucfirst($field)." must be {$maxLength} characters or fewer.";
        }
    }

    private static function optionalString(array $input, string $field, int $maxLength, array &$errors): void
    {
        if (! array_key_exists($field, $input) || $input[$field] === null) {
            return;
        }
        if (! is_string($input[$field])) {
            $errors[$field] = ucfirst($field).' must be a string.';
            return;
        }
        if (strlen($input[$field]) > $maxLength) {
            $errors[$field] = ucfirst($field)." must be {$maxLength} characters or fewer.";
        }
    }
}
