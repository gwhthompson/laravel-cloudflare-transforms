<?php

declare(strict_types=1);

/** @return array<string, mixed> */
return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare Transforms Domain (Fallback)
    |--------------------------------------------------------------------------
    |
    | Fallback domain for CloudflareImage::make() when not using Storage macros.
    |
    | For Storage disks, configure the 'url' option in your filesystems.php:
    |
    |     's3' => [
    |         'driver' => 's3',
    |         'url' => env('CLOUDFLARE_CDN_URL'), // e.g., https://cdn.example.com
    |         // ... other S3 options
    |     ],
    |
    */
    'domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default disk to use for CloudflareImage file validation when using
    | CloudflareImage::make() directly (not via Storage macros).
    |
    */
    'disk' => env('CLOUDFLARE_TRANSFORMS_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Transform Path
    |--------------------------------------------------------------------------
    |
    | The URL path for Cloudflare Image Transformations. Usually 'cdn-cgi/image'
    | for most Cloudflare setups, or 'image' for custom configurations.
    |
    */
    'transform_path' => env('CLOUDFLARE_TRANSFORMS_PATH', 'cdn-cgi/image'),

    /*
    |--------------------------------------------------------------------------
    | Validate File Exists
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will check if the file exists in storage before
    | generating the transformation URL. Disable this for better performance
    | when you're confident files exist (e.g., high-traffic production sites).
    |
    | Note: Disabling this means broken image URLs won't be caught at generation
    | time - they'll instead return Cloudflare errors at request time.
    |
    */
    'validate_file_exists' => env('CLOUDFLARE_VALIDATE_FILE_EXISTS', true),
];
