# CLAUDE.md

## Commands

```bash
composer test      # Pest tests
composer lint      # Pint (Laravel preset)
composer analyse   # PHPStan (level max)
```

## Architecture

Laravel package for Cloudflare Image Transformation URLs.

### Core Classes

- **CloudflareImage** — Fluent URL builder. `make()` creates, `url()` generates.
- **NullCloudflareImage** — Null object for non-Cloudflare disks.
- **CloudflareTransformsServiceProvider** — Registers `image()` and `cloudflareUrl()` Storage macros.
- **Enums** — Fit, Format, Quality, Gravity, Flip, Metadata.

### Flow

1. `Storage::disk('x')->image('path')` or `CloudflareImage::make('path')`
2. Chain transforms: `->width(400)->format(Format::Auto)`
3. `->url()` returns: `https://{domain}/cdn-cgi/image/{transforms}/{path}`

## Code Standards

PHP 8.4+, strict types, PHPStan max, Pint Laravel preset.
