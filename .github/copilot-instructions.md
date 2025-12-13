# Project

Laravel package for Cloudflare Image Transformation URLs.
PHP 8.4+, Laravel 12+, PHPStan level MAX (9).

## Commands

- `composer test` — Pest 4 tests
- `composer lint` — Laravel Pint
- `composer analyse` — PHPStan (must pass)

## Code Style

Every PHP file requires `declare(strict_types=1);` as first statement.
All parameters and return types must be explicitly typed for PHPStan MAX.

Use this validation pattern:
```php
return $value >= 1 && $value <= 100
    ? $this->with('key', $value)
    : throw new InvalidArgumentException('Value must be 1-100');
```

## Structure

- `src/` — Package source (CloudflareImage fluent builder, enums, service provider)
- `tests/` — Pest tests with datasets
- Namespace: `Gwhthompson\CloudflareTransforms`

## Boundaries

- Always: Type all parameters and returns, validate inputs, throw exceptions with clear messages
- Ask first: Architecture changes, new dependencies
- Never: Silent failures, missing strict types, untyped code
