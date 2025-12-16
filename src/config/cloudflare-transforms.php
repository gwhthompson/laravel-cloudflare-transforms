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
    | When using Storage macros, the domain is extracted from the disk's 'url'
    | config. This works with ANY disk driver (s3, local, ftp, etc.):
    |
    |     'media' => [
    |         'driver' => 's3',  // or any driver
    |         'url' => env('CLOUDFLARE_CDN_URL'), // e.g., https://cdn.example.com
    |         // ... other options
    |     ],
    |
    | The 'url' must point to a Cloudflare-proxied domain with Image
    | Transformations enabled on that zone.
    |
    */
    'domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk (for direct CloudflareImage::make() only)
    |--------------------------------------------------------------------------
    |
    | The default disk for file validation when using CloudflareImage::make()
    | directly. This is NOT used by Storage macros - they validate on the
    | disk the macro was called on.
    |
    | Example: Storage::disk('b2')->image('photo.jpg') validates on 'b2' disk.
    |
    */
    'disk' => env('CLOUDFLARE_TRANSFORMS_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Transform Path
    |--------------------------------------------------------------------------
    |
    | The URL path for Cloudflare Image Transformations.
    |
    | Default 'cdn-cgi/image' is Cloudflare's standard path. You can use a
    | custom path (e.g., 'image') with Cloudflare URL Rewrite Rules to map
    | your custom path to '/cdn-cgi/image'.
    |
    | Example rewrite rule for '/image' path:
    |   Filter: starts_with(path, "/image")
    |           AND NOT any(http.request.headers["via"][*] contains "image-resizing")
    |   Rewrite: concat("/cdn-cgi/image", substring(path, 6))
    |
    */
    'transform_path' => env('CLOUDFLARE_TRANSFORMS_PATH', 'cdn-cgi/image'),

    /*
    |--------------------------------------------------------------------------
    | Validate File Exists
    |--------------------------------------------------------------------------
    |
    | When enabled, the package checks if the file exists before generating
    | the transformation URL.
    |
    | Disable this when:
    | - Files are stored on a backend Laravel can't access directly (e.g., B2
    |   accessed via Cloudflare Worker, external URLs)
    | - You want better performance on high-traffic sites
    | - You're confident files exist
    |
    | When disabled, broken image URLs won't be caught at generation time -
    | they'll return Cloudflare errors at request time instead.
    |
    */
    'validate_file_exists' => env('CLOUDFLARE_VALIDATE_FILE_EXISTS', true),
];
