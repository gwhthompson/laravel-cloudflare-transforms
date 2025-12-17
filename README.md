# Laravel Cloudflare Transforms

[![Tests](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml/badge.svg)](https://github.com/gwhthompson/laravel-cloudflare-transforms/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/gwhthompson/laravel-cloudflare-transforms/graph/badge.svg)](https://codecov.io/gh/gwhthompson/laravel-cloudflare-transforms)
[![Latest Version](https://img.shields.io/packagist/v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![PHP Version](https://img.shields.io/packagist/php-v/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)
[![License](https://img.shields.io/packagist/l/gwhthompson/laravel-cloudflare-transforms.svg)](https://packagist.org/packages/gwhthompson/laravel-cloudflare-transforms)

Fluent API for Cloudflare Image Transformation URLs.

## Features

- Fluent, chainable API for all Cloudflare image transformations
- Laravel Storage integration via macros (works with any disk driver)
- Type-safe enums for fit, format, quality, gravity, flip, and metadata
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

## How It Works

```
Laravel stores:    venues/photo.jpg
                   └─── storage path ───┘

Package generates: https://cdn.example.com/cdn-cgi/image/w=400/venues/photo.jpg
                   └──── zone ─────────┘ └─ transform path ─┘    └─ source path ─┘
```

1. Package registers `image()` and `cloudflareUrl()` macros on Laravel's `FilesystemAdapter`
2. When called, extracts domain from the disk's `url` config
3. Generates transform URL using Laravel storage path as the source path
4. Cloudflare fetches the original image from the **same zone** and applies transforms
5. If no Cloudflare domain found, returns the disk's native URL unchanged (graceful fallback)

**Key requirement:** The disk's `url` config must point to a Cloudflare-proxied domain with Image Transformations enabled.

## Cloudflare Requirements

### Pricing

- **Free:** 5,000 unique transformations per month
- **Paid:** $0.50 per 1,000 additional transformations

### Setup

1. Go to Cloudflare dashboard → **Images** → **Transformations**
2. Select your zone → **Enable for zone**

### Origin Requirements

Cloudflare fetches the original image from your zone's origin at the **same path** as the source path in the transform URL:

| Transform URL | Origin Fetch |
|---------------|--------------|
| `https://cdn.example.com/cdn-cgi/image/w=400/venues/photo.jpg` | `https://cdn.example.com/venues/photo.jpg` |

Your origin must serve files at the Laravel storage path. This works automatically for:
- **S3, B2, R2** — files served at storage paths
- **Local disk** — may need web server routes or URL rewrite rules

By default, Cloudflare only transforms images from the **same zone**. To transform images from other origins, configure "Allowed Origins" in the Cloudflare dashboard.

## Configuration

Set your disk's `url` to your Cloudflare domain:

```php
// config/filesystems.php
'media' => [
    'driver' => 's3',  // Works with any driver: s3, local, ftp, etc.
    // ... driver-specific config
    'url' => env('CLOUDFLARE_CDN_URL', 'https://cdn.example.com'),
],
```

The package extracts the domain from this URL automatically.

Publish the config file for additional options:

```bash
php artisan vendor:publish --tag=cloudflare-transforms-config
```

| Option | Default | Description |
|--------|---------|-------------|
| `domain` | `null` | Fallback domain for `CloudflareImage::make()` direct usage |
| `disk` | `s3` | Default disk for validation with `CloudflareImage::make()` |
| `transform_path` | `cdn-cgi/image` | URL path for transformations |
| `validate_file_exists` | `true` | Check file exists before generating URL |

## Storage Backend Examples

### S3 behind Cloudflare CDN

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('CLOUDFLARE_CDN_URL'),  // e.g., https://cdn.example.com
],
```

### Backblaze B2 via Cloudflare Worker

```php
'b2' => [
    'driver' => 's3',  // B2 is S3-compatible
    'key' => env('B2_ACCESS_KEY_ID'),
    'secret' => env('B2_SECRET_ACCESS_KEY'),
    'region' => env('B2_REGION'),
    'bucket' => env('B2_BUCKET'),
    'endpoint' => env('B2_ENDPOINT'),
    'url' => env('CLOUDFLARE_CDN_URL'),  // e.g., https://media.example.com
],
```

For B2 private buckets, you'll need a Cloudflare Worker to handle authentication. The Worker authenticates requests to B2 while Cloudflare handles transforms.

### Cloudflare R2

```php
'r2' => [
    'driver' => 's3',
    'key' => env('R2_ACCESS_KEY_ID'),
    'secret' => env('R2_SECRET_ACCESS_KEY'),
    'region' => 'auto',
    'bucket' => env('R2_BUCKET'),
    'endpoint' => env('R2_ENDPOINT'),
    'url' => env('CLOUDFLARE_CDN_URL'),  // e.g., https://assets.example.com
],
```

## Custom Transform Path

By default, Cloudflare uses `/cdn-cgi/image/` for transforms. You can use a custom path like `/image/` with URL Rewrite Rules:

1. Set `transform_path` to `image` in the package config
2. Add a Cloudflare URL Rewrite Rule:

```
Filter:  starts_with(http.request.uri.path, "/image")
         AND NOT any(http.request.headers["via"][*] contains "image-resizing")

Rewrite: concat("/cdn-cgi/image", substring(http.request.uri.path, 6))
```

The `via: image-resizing` check prevents infinite loops when Cloudflare fetches the original image.

## Usage

```php
// Via Storage macro (recommended)
Storage::disk('media')->image('photo.jpg')
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
Storage::disk('media')->image('hero.jpg')
    ->width(1200)
    ->height(630)
    ->fit(Fit::Cover)
    ->gravity(Gravity::Face)
    ->format(Format::Auto)
    ->quality(Quality::High)
    ->url();

// Array-based
Storage::disk('media')->cloudflareUrl('photo.jpg', ['width' => 400]);

// Via Facade (alternative to Storage macro)
use GwhThompson\CloudflareTransforms\Facades\CloudflareImage;

CloudflareImage::make('photo.jpg')->width(400)->url();
```

## Transformations

The package supports all [Cloudflare Image Transformations](https://developers.cloudflare.com/images/transform-images/transform-via-url/) with fluent methods.

### Type-Safe Enums

| Enum | Values |
|------|--------|
| `Fit` | Contain, Cover, Crop, Pad, ScaleDown, Squeeze |
| `Format` | Auto, Avif, BaselineJpeg, Jpeg, Json, Webp |
| `Quality` | High, MediumHigh, MediumLow, Low |
| `Gravity` | Auto, Top, Bottom, Left, Right, Face |
| `Flip` | Horizontal, Vertical, Both |
| `Metadata` | Keep, Copyright, None |

### Convenience Methods

| Method | Effect |
|--------|--------|
| `optimize()` | format=auto + quality=high |
| `thumbnail(150)` | Square crop at size |
| `responsive(400, 2)` | width + dpr + format=auto |
| `grayscale()` | Removes colour |
| `srcset([320, 640, 960])` | Width breakpoints for responsive images |
| `srcsetDensity(400)` | 1x/2x density variants |

## Blade Component

Render responsive Cloudflare-transformed images directly in Blade:

```blade
<x-cloudflare:image
    path="hero.jpg"
    :width="1200"
    :height="630"
    :fit="Fit::Cover"
    :format="Format::Auto"
    :srcset="[320, 640, 960, 1280]"
    sizes="(max-width: 640px) 100vw, 50vw"
/>
```

Generates:
```html
<img src="https://cdn.example.com/cdn-cgi/image/w=1280,h=630,fit=cover,f=auto/hero.jpg"
     srcset="...320w, ...640w, ...960w, ...1280w"
     sizes="(max-width: 640px) 100vw, 50vw">
```

| Prop | Type | Description |
|------|------|-------------|
| `path` | string | Storage path to image (required) |
| `disk` | string | Storage disk name |
| `width` | int | Width in pixels |
| `height` | int | Height in pixels |
| `format` | Format | Output format (Auto, Webp, Avif, etc.) |
| `fit` | Fit | Resize mode (Cover, Contain, Crop, etc.) |
| `gravity` | Gravity\|string | Focal point for cropping |
| `quality` | Quality\|int | Output quality |
| `srcset` | array | Width breakpoints for responsive images |
| `srcsetDensity` | int | Base width for 1x/2x density variants |
| `sizes` | string | HTML sizes attribute |

All additional HTML attributes are forwarded to the `<img>` tag.

## Responsive Images

Generate srcset strings for responsive images:

```php
// Width-based breakpoints
Storage::disk('media')->image('hero.jpg')
    ->srcset([320, 640, 960, 1280]);
// → "https://.../w=320/hero.jpg 320w, .../w=640/hero.jpg 640w, ..."

// Density-based variants (1x, 2x)
Storage::disk('media')->image('hero.jpg')
    ->srcsetDensity(400);
// → "https://.../w=400/hero.jpg 1x, .../w=800/hero.jpg 2x"
```

## Testing

In your test environment, disable file validation:

```bash
# .env.testing
CLOUDFLARE_VALIDATE_FILE_EXISTS=false
```

## Security

If you discover a security vulnerability, please email security@unfetter.co instead of using the issue tracker.

## License

MIT
