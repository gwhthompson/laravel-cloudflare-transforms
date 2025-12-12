# Laravel Cloudflare Transforms

A Laravel package for generating Cloudflare Image Transformation URLs with a fluent API.

## Prerequisites

This package requires [Cloudflare Image Transformations](https://developers.cloudflare.com/images/transform-images/) enabled on your zone:

1. Add your domain to Cloudflare
2. In Cloudflare dashboard: **Images** → **Transformations** → **Enable for zone**

## Installation

```bash
composer require gwhthompson/laravel-cloudflare-transforms
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Gwhthompson\CloudflareTransforms\CloudflareTransformsServiceProvider"
```

## Configuration

Add `cloudflare_domain` to your S3 disk in `config/filesystems.php`:

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'cloudflare_domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),
],
```

Set your Cloudflare zone domain in `.env`:

```env
CLOUDFLARE_TRANSFORMS_DOMAIN=cdn.example.com
```

## Usage

### Basic Usage

```php
use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\Enums\Format;

$url = CloudflareImage::make('photo.jpg')
    ->width(300)
    ->height(200)
    ->format(Format::Webp)
    ->quality(80)
    ->url();

// https://cdn.example.com/cdn-cgi/image/w=300,h=200,f=webp,q=80/photo.jpg
```

### Storage Integration

```php
use Illuminate\Support\Facades\Storage;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;

// Fluent API
$url = Storage::disk('s3')->image('photo.jpg')
    ->width(400)
    ->fit(Fit::Cover)
    ->format(Format::Auto)
    ->url();

// Array-based API
$url = Storage::disk('s3')->cloudflareUrl('photo.jpg', [
    'width' => 400,
    'height' => 300,
]);
```

## How It Works

This package extends Laravel's `s3` driver to add Cloudflare CDN URL generation:

- Uses `AwsS3V3Adapter` under the hood for full S3 compatibility
- Returns Cloudflare CDN URLs when `cloudflare_domain` is configured
- Falls back to standard S3 URLs when `cloudflare_domain` is not set
- Works with Filament, Livewire, and all Laravel packages expecting an S3 driver

## Testing

```bash
composer test
composer lint
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
