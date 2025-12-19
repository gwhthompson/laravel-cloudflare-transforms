<?php

declare(strict_types=1);

/** @return array<string, mixed> */
return [
    // Fallback domain for CloudflareImage::make() (macros extract from disk url)
    'domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),

    // Default disk for CloudflareImage::make() file validation
    'disk' => env('CLOUDFLARE_TRANSFORMS_DISK', 's3'),

    // URL path for transforms (use rewrite rules for custom paths)
    'transform_path' => env('CLOUDFLARE_TRANSFORMS_PATH', 'cdn-cgi/image'),

    // Check file exists before generating URL (disable for external files or performance)
    'validate_file_exists' => env('CLOUDFLARE_VALIDATE_FILE_EXISTS', true),
];
