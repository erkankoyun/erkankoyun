<?php

declare(strict_types=1);

namespace StockFlow\Support;

final class Pagination
{
    /** @return array{page:int,per_page:int,offset:int} */
    public static function fromQuery(array $query): array
    {
        $page = filter_var($query['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $perPage = filter_var($query['per_page'] ?? 10, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        $page = $page === false ? 1 : $page;
        $perPage = $perPage === false ? 10 : $perPage;

        return ['page' => $page, 'per_page' => $perPage, 'offset' => ($page - 1) * $perPage];
    }
}
