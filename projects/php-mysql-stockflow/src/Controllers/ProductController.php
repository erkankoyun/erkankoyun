<?php

declare(strict_types=1);

namespace StockFlow\Controllers;

use StockFlow\Http\JsonResponse;
use StockFlow\Repositories\ProductRepository;
use StockFlow\Support\Pagination;
use StockFlow\Validation\ProductValidator;

final class ProductController
{
    private const STATUSES = ['active', 'inactive', 'discontinued'];
    public function __construct(private readonly ProductRepository $products) {}

    public function index(array $query): never
    {
        $status = trim((string) ($query['status'] ?? ''));
        if ($status !== '' && ! in_array($status, self::STATUSES, true)) {
            JsonResponse::error('Invalid status filter.', 422, ['status' => 'Use active, inactive, or discontinued.']);
        }
        $pagination = Pagination::fromQuery($query);
        JsonResponse::send($this->products->paginate($query, $pagination['page'], $pagination['per_page'], $pagination['offset']));
    }

    public function show(int $id): never
    {
        $product = $this->products->find($id);
        if ($product === null) {
            JsonResponse::error('Product not found.', 404);
        }
        JsonResponse::send(['data' => $product]);
    }

    public function store(array $input): never
    {
        $errors = ProductValidator::validate($input);
        if ($errors !== []) {
            JsonResponse::error('Validation failed.', 422, $errors);
        }
        if ($this->products->skuExists((string) $input['sku'])) {
            JsonResponse::error('Validation failed.', 422, ['sku' => 'SKU must be unique.']);
        }
        JsonResponse::send(['data' => $this->products->create($input)], 201);
    }

    public function update(int $id, array $input): never
    {
        if ($this->products->find($id) === null) {
            JsonResponse::error('Product not found.', 404);
        }
        if ($input === []) {
            JsonResponse::error('Request body must contain at least one product field.', 422);
        }
        $errors = ProductValidator::validate($input, true);
        if ($errors !== []) {
            JsonResponse::error('Validation failed.', 422, $errors);
        }
        if (isset($input['sku']) && $this->products->skuExists((string) $input['sku'], $id)) {
            JsonResponse::error('Validation failed.', 422, ['sku' => 'SKU must be unique.']);
        }
        JsonResponse::send(['data' => $this->products->update($id, $input)]);
    }

    public function destroy(int $id): never
    {
        if (! $this->products->delete($id)) {
            JsonResponse::error('Product not found.', 404);
        }
        JsonResponse::send(['message' => 'Product deleted.']);
    }
}
