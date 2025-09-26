# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with this repository.

## Package Overview

Laravel package (`gwhthompson/laravel-cloudflare-transforms`) for Cloudflare Image Transformations with fluent API and Storage integration.

## Commands

### Testing
```bash
composer test        # Run Pest tests
composer lint        # Laravel Pint code style
composer analyse     # PHPStan level 9 analysis
```

## Architecture

### Core Classes
- **CloudflareImage** - Fluent API builder for transformation URLs
- **CloudflareFilesystemAdapter** - Custom adapter with Cloudflare URL generation
- **NullCloudflareImage** - Null object for non-Cloudflare disks
- **CloudflareTransformsServiceProvider** - Driver registration & Storage macros

### Testing Approach
- Pest test framework with Orchestra Testbench
- Storage::fake() for filesystem mocking
- Comprehensive coverage: unit tests + integration tests
- PHPStan level 9 compliance with strict_types everywhere

### Code Standards
- PHP 8.4 + Laravel 12.0
- Strict types in all files
- Laravel Pint with custom rules (see `pint.json`)
- Type-safe enums for transformation options

## Troubleshooting

**PHPStan Issues**: Ensure all files have `declare(strict_types=1)` and proper type annotations
**Test Failures**: Use `Storage::fake()` for filesystem tests, check config values in test setup
**Transformation URLs**: Verify `CLOUDFLARE_TRANSFORMS_DOMAIN` is set and transform_path config is correct