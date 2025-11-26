# Laravel Cloudflare Transforms

A Laravel package for generating Cloudflare Image Transformation URLs with a fluent API.

## Installation

```bash
composer require gwhthompson/laravel-cloudflare-transforms
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Gwhthompson\CloudflareTransforms\CloudflareTransformsServiceProvider"
```

Configure your environment variables:

```env
CLOUDFLARE_TRANSFORMS_DOMAIN=your-cdn-domain.com
```

### Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| `domain` | Cloudflare CDN domain | Required |
| `disk` | Default storage disk | `public` |
| `transform_path` | Transformation URL path | `cdn-cgi/image` |
| `auto_transform.enabled` | Enable auto transformations | `true` |

## Usage

### Basic Usage

```php
use Gwhthompson\CloudflareTransforms\CloudflareImage;

$image = CloudflareImage::make('uploads/photo.jpg')
    ->width(300)
    ->height(200)
    ->format('webp')
    ->quality(80);

echo $image->url();
// https://your-cdn.com/cdn-cgi/image/width=300,height=200,format=webp,quality=80/uploads/photo.jpg
```

### Storage Integration

```php
use Illuminate\Support\Facades\Storage;

// Using the image() macro
$image = Storage::disk('cloudflare')->image('photo.jpg')
    ->width(400)
    ->fit('cover');

// Using the cloudflareUrl() macro
$url = Storage::disk('cloudflare')->cloudflareUrl('photo.jpg', [
    'width' => 400,
    'height' => 300
]);
```

### Filesystem Configuration

Add an S3 disk with Cloudflare support to your `config/filesystems.php`:

```php
'cloudflare' => [
    'driver' => 's3',  // Standard S3 driver - package enhances it automatically
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    // Cloudflare-specific (optional - enables CDN URLs)
    'cloudflare_domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),
],
```

**Note:** When `cloudflare_domain` is set, `url()` returns Cloudflare CDN URLs. When not set, it falls back to standard S3 URL generation.

## How It Works

This package registers as the `s3` driver, making it a **superset** of Laravel's built-in S3 driver:

- Uses `AwsS3V3Adapter` under the hood for full S3 compatibility
- Falls back to standard S3 behavior when `cloudflare_domain` is not configured
- Automatically returns Cloudflare CDN URLs when `cloudflare_domain` is set
- Works seamlessly with Filament, Livewire, and all S3-aware Laravel code

## Upgrading

### From v1.x to v2.x

The package now registers as the standard `s3` driver instead of a custom `cloudflare-s3` driver.

**Before (v1.x):**
```php
'cloudflare' => [
    'driver' => 'cloudflare-s3',  // Custom driver name
    'cloudflare_domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),
    // ...
],
```

**After (v2.x):**
```php
'cloudflare' => [
    'driver' => 's3',  // Standard S3 driver
    'cloudflare_domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),
    // ...
],
```

This change ensures compatibility with all Laravel packages that check for `driver === 's3'`.

## Testing

```bash
composer test
composer lint
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
