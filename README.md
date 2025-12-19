# Laravel Cloudflare Transforms

[![Tests](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml/badge.svg)](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/gwhthompson/laravel-cloudflare-transforms/graph/badge.svg)](https://codecov.io/gh/gwhthompson/laravel-cloudflare-transforms)
[![Latest Version](https://img.shields.io/packagist/v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![License](https://img.shields.io/packagist/l/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![Total Downloads](https://img.shields.io/packagist/dt/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![PHP Version](https://img.shields.io/packagist/php-v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)

Fluent API for [Cloudflare Image Transformations](https://developers.cloudflare.com/images/transform-images/).

## Installation

Requires PHP 8.3+ and Laravel 11+.

```bash
composer require gwhthompson/laravel-cloudflare-transforms
```

Set your disk's `url` to a Cloudflare-proxied domain:

```php
// config/filesystems.php
'media' => [
    'driver' => 's3',
    'url' => env('CLOUDFLARE_CDN_URL'), // https://cdn.example.com
    // ...
],
```

## Usage

```php
Storage::disk('media')->image('photo.jpg')
    ->width(400)
    ->format(Format::Auto)
    ->url();
// → https://cdn.example.com/cdn-cgi/image/w=400,f=auto/photo.jpg

// Responsive srcset
Storage::disk('media')->image('hero.jpg')
    ->srcset([320, 640, 960, 1280]);

// Convenience methods
->optimize()     // format=auto, quality=high
->thumbnail(150) // square crop
->grayscale()
```

## Blade Component

```blade
<x-cloudflare:image
    path="hero.jpg"
    :width="1200"
    :fit="Fit::Cover"
    :srcset="[320, 640, 960, 1280]"
    sizes="(max-width: 640px) 100vw, 50vw"
/>
```

## Enums

| Enum | Values |
|------|--------|
| `Fit` | Contain, Cover, Crop, Pad, ScaleDown, Squeeze |
| `Format` | Auto, Avif, Jpeg, Webp |
| `Quality` | High, MediumHigh, MediumLow, Low |
| `Gravity` | Auto, Top, Bottom, Left, Right, Face |

## Configuration

```bash
php artisan vendor:publish --tag=cloudflare-transforms-config
```

| Option | Default | Purpose |
|--------|---------|---------|
| `domain` | `null` | Fallback for `CloudflareImage::make()` |
| `transform_path` | `cdn-cgi/image` | Custom path with URL rewrite |
| `validate_file_exists` | `true` | Disable for performance |

## Testing

```bash
# .env.testing
CLOUDFLARE_VALIDATE_FILE_EXISTS=false
```

## Security

Report vulnerabilities to security@unfetter.co.

## License

MIT
