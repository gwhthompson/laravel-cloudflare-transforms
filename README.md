# Laravel Cloudflare Transforms

[![Tests](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml/badge.svg)](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![PHP Version](https://img.shields.io/packagist/php-v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![License](https://img.shields.io/packagist/l/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)

Fluent API for Cloudflare Image Transformation URLs.

## Features

- Fluent, chainable API for all Cloudflare image transformations
- Laravel Storage integration via macros
- Type-safe enums for fit, format, quality, and gravity options
- Graceful fallback on non-Cloudflare disks (returns original URL)
- PHPStan level max strict typing

## Requirements

- PHP 8.4+
- Laravel 12+
- [Cloudflare Image Transformations](https://developers.cloudflare.com/images/transform-images/) enabled on your zone

## Installation

```bash
composer require gwhthompson/laravel-cloudflare-transforms
```

## Configuration

Set your S3 disk's `url` to your Cloudflare CDN domain:

```php
// config/filesystems.php
's3' => [
    'driver' => 's3',
    // ... standard S3 config
    'url' => env('AWS_URL', 'https://cdn.example.com'),
],
```

The package extracts the domain from this URL automatically.

Publish the config file for additional options:

```bash
php artisan vendor:publish --tag=cloudflare-transforms-config
```

| Option | Default | Description |
|--------|---------|-------------|
| `domain` | `null` | Fallback domain for `CloudflareImage::make()` |
| `disk` | `s3` | Default storage disk for file validation |
| `transform_path` | `cdn-cgi/image` | URL path for transformations |
| `validate_file_exists` | `true` | Check file exists before generating URL |

## Usage

```php
// Via Storage macro (recommended)
Storage::disk('s3')->image('photo.jpg')
    ->width(400)
    ->format(Format::Auto)
    ->url();

// Direct usage
CloudflareImage::make('photo.jpg')
    ->width(300)
    ->height(200)
    ->format(Format::Webp)
    ->url();
// → https://cdn.example.com/cdn-cgi/image/w=300,h=200,f=webp/photo.jpg

// Complete example with multiple transforms
Storage::disk('s3')->image('hero.jpg')
    ->width(1200)
    ->height(630)
    ->fit(Fit::Cover)
    ->gravity(Gravity::Face)
    ->format(Format::Auto)
    ->quality(Quality::High)
    ->url();

// Array-based
Storage::disk('s3')->cloudflareUrl('photo.jpg', ['width' => 400]);
```

## Transformations

All [Cloudflare Image Transformations](https://developers.cloudflare.com/images/transform-images/transform-via-url/) are supported via fluent methods.

Convenience methods for common patterns:

- `optimize()` — format=auto + quality=high
- `thumbnail(150)` — square crop at size
- `responsive(400, 2)` — width + dpr + format=auto
- `grayscale()` — removes color

## License

MIT
