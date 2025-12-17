# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer test      # Run Pest tests
composer lint      # Run Pint linter (Laravel preset)
composer analyse   # Run PHPStan static analysis (level max)
```

## Architecture

This is a Laravel package that generates Cloudflare Image Transformation URLs with a fluent API.

### Core Components

- **CloudflareImage** (`src/CloudflareImage.php`): Main fluent API builder for constructing Cloudflare transformation URLs. Contains chainable methods for all Cloudflare image transformation parameters (width, height, format, quality, fit, etc.). The `make()` static method creates instances, and `url()` generates the final transformed URL.

- **CloudflareTransformsServiceProvider** (`src/CloudflareTransformsServiceProvider.php`): Registers Storage macros via `FilesystemAdapter::macro()`. The `image()` macro returns a `CloudflareImage` (or `NullCloudflareImage` for non-Cloudflare disks). The `cloudflareUrl()` macro provides array-based transformation options.

- **NullCloudflareImage** (`src/NullCloudflareImage.php`): Null object pattern implementation. Returns the original URL unchanged, allowing code to use the fluent API without conditional checks on non-Cloudflare disks.

- **Enums** (`src/Enums/`): Type-safe enums for transformation parameters (Fit, Format, Quality, Gravity, Flip, Metadata).

### Flow

1. User creates CloudflareImage via `CloudflareImage::make()` or Storage macro
2. Chains transformation methods (width, height, format, etc.)
3. Calls `url()` which builds: `https://{domain}/{transform_path}/{transforms}/{path}`
4. Transform path defaults to `cdn-cgi/image` with comma-separated transformations

## Code Standards

- PHP 8.4+ with strict types (`declare(strict_types=1)`)
- PHPStan level max type safety
- Laravel Pint with Laravel preset for formatting
- Rector configured for Laravel 12, strict typing, and code quality rules
- All methods should have return types and parameter types