<?php

/** @return array<string, mixed> */
return [
    /*
    |--------------------------------------------------------------------------
    | Cloudflare Transforms Domain
    |--------------------------------------------------------------------------
    |
    | The domain that serves your files through Cloudflare's CDN. This domain
    | should have Cloudflare Image Transformations enabled.
    |
    */
    'domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The default disk to use for CloudflareImage transformations when no
    | disk is explicitly specified. This should match your configured disk name.
    |
    */
    'disk' => env('CLOUDFLARE_TRANSFORMS_DISK', 'public'),

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
    | Auto Transform Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic image transformations.
    |
    */
    'auto_transform' => [
        'enabled' => env('CLOUDFLARE_AUTO_TRANSFORM', true),
        'default_format' => 'auto',
        'default_quality' => 85,
    ],
];
