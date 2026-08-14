# StockFlow API — PHP/MySQL Backend Project

A framework-free PHP 8.2+ REST API demonstrating native PHP backend development with MySQL, PDO prepared statements, validation, pagination, search, API-key protected write operations, Docker, and automated tests.

## Features

- PHP 8.2+ with PSR-4 autoloading
- MySQL 8 schema with indexes and unique SKU constraint
- PDO prepared statements
- REST-style JSON endpoints
- Search, status filtering, and pagination
- Product CRUD operations
- Input validation and consistent JSON errors
- API-key protection for write endpoints
- Environment-based configuration
- Docker Compose development stack
- PHPUnit tests and GitHub Actions CI

## Endpoints

| Method | Endpoint | Access |
|---|---|---|
| `GET` | `/health` | Public |
| `GET` | `/api/products` | Public |
| `GET` | `/api/products/{id}` | Public |
| `POST` | `/api/products` | `X-API-Key` |
| `PUT` / `PATCH` | `/api/products/{id}` | `X-API-Key` |
| `DELETE` | `/api/products/{id}` | `X-API-Key` |

Example search:

```http
GET /api/products?search=keyboard&status=active&page=1&per_page=10
```

Example create request:

```bash
curl -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -H "X-API-Key: local-development-key" \
  -d '{"sku":"KB-100","name":"Mechanical Keyboard","price":89.99,"quantity":12,"status":"active"}'
```

## Run with Docker

```bash
cp .env.example .env
docker compose up --build
```

Then visit:

```text
http://localhost:8080/health
```

MySQL initializes from `database/schema.sql` and `database/seed.sql` on the first clean start.

## Tests

```bash
composer install
composer test
```

## Security notes

Write operations require an API key and SQL values use PDO prepared statements. The small API-key guard is intentionally portfolio-sized; a production service would use identity-based authentication, scoped tokens, rate limiting, centralized secret management, and stronger authorization.

## Author

**Erkan Koyun**  
Software Developer | PHP • Laravel • Python | Backend Development | IT Specialist
