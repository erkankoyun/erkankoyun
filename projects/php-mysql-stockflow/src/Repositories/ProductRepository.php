<?php

declare(strict_types=1);

namespace StockFlow\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function paginate(array $filters, int $page, int $perPage, int $offset): array
    {
        $where = [];
        $params = [];
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = '(sku LIKE :search OR name LIKE :search OR description LIKE :search)';
            $params['search'] = '%'.$search.'%';
        }
        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        $whereSql = $where === [] ? '' : ' WHERE '.implode(' AND ', $where);

        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM products'.$whereSql);
        foreach ($params as $key => $value) {
            $countStatement->bindValue(':'.$key, $value, PDO::PARAM_STR);
        }
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $statement = $this->pdo->prepare('SELECT id, sku, name, description, price, quantity, status, created_at, updated_at FROM products'.$whereSql.' ORDER BY id DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) {
            $statement->bindValue(':'.$key, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'data' => array_map([$this, 'normalizeRow'], $statement->fetchAll()),
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
            ],
        ];
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, sku, name, description, price, quantity, status, created_at, updated_at FROM products WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $row = $statement->fetch();
        return $row === false ? null : $this->normalizeRow($row);
    }

    public function skuExists(string $sku, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM products WHERE sku = :sku';
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
        }
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':sku', trim($sku), PDO::PARAM_STR);
        if ($exceptId !== null) {
            $statement->bindValue(':id', $exceptId, PDO::PARAM_INT);
        }
        $statement->execute();
        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $input): array
    {
        $statement = $this->pdo->prepare('INSERT INTO products (sku, name, description, price, quantity, status) VALUES (:sku, :name, :description, :price, :quantity, :status)');
        $statement->execute([
            'sku' => trim((string) $input['sku']),
            'name' => trim((string) $input['name']),
            'description' => isset($input['description']) ? trim((string) $input['description']) : null,
            'price' => number_format((float) $input['price'], 2, '.', ''),
            'quantity' => (int) $input['quantity'],
            'status' => (string) $input['status'],
        ]);
        return $this->find((int) $this->pdo->lastInsertId()) ?? [];
    }

    public function update(int $id, array $input): ?array
    {
        if ($input === []) {
            return $this->find($id);
        }
        $allowed = ['sku', 'name', 'description', 'price', 'quantity', 'status'];
        $sets = [];
        $params = ['id' => $id];
        foreach ($allowed as $field) {
            if (! array_key_exists($field, $input)) {
                continue;
            }
            $sets[] = $field.' = :'.$field;
            $params[$field] = match ($field) {
                'price' => number_format((float) $input[$field], 2, '.', ''),
                'quantity' => (int) $input[$field],
                'sku', 'name' => trim((string) $input[$field]),
                'description' => $input[$field] === null ? null : trim((string) $input[$field]),
                default => (string) $input[$field],
            };
        }
        if ($sets === []) {
            return $this->find($id);
        }
        $statement = $this->pdo->prepare('UPDATE products SET '.implode(', ', $sets).', updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $statement->execute($params);
        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->rowCount() > 0;
    }

    private function normalizeRow(array $row): array
    {
        return [
            'id' => (int) $row['id'], 'sku' => (string) $row['sku'], 'name' => (string) $row['name'],
            'description' => $row['description'], 'price' => (float) $row['price'], 'quantity' => (int) $row['quantity'],
            'status' => (string) $row['status'], 'created_at' => (string) $row['created_at'], 'updated_at' => (string) $row['updated_at'],
        ];
    }
}
