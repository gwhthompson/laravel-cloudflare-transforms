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
CLOUDFLARE_TRANSFORMS_DISK=cloudflare-s3
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
$image = Storage::disk('cloudflare-s3')->image('photo.jpg')
    ->width(400)
    ->fit('cover');

// Using the cloudflareUrl() macro
$url = Storage::disk('cloudflare-s3')->cloudflareUrl('photo.jpg', [
    'width' => 400,
    'height' => 300
]);
```

### Filesystem Configuration

Add a cloudflare-s3 disk to your `config/filesystems.php`:

```php
'cloudflare-s3' => [
    'driver' => 'cloudflare-s3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),
    'cloudflare_domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),
],
```

## Testing

```bash
composer test
composer lint
composer analyse
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.