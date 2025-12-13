# Laravel Cloudflare Transforms

[![Tests](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml/badge.svg)](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![PHP Version](https://img.shields.io/packagist/php-v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![License](https://img.shields.io/packagist/l/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)

Fluent API for Cloudflare Image Transformation URLs.

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

// Array-based
Storage::disk('s3')->cloudflareUrl('photo.jpg', ['width' => 400]);
```

## Graceful Fallback

Disks without a `url` config return unmodified URLs (no transformations applied).

## Static Analysis

Works automatically with [Larastan](https://github.com/larastan/larastan) - no configuration needed.

## License

MIT
