<?php

return [
    'domain' => env('CLOUDFLARE_TRANSFORMS_DOMAIN'),
    'disk' => env('CLOUDFLARE_TRANSFORMS_DISK', 'public'),
    'transform_path' => 'cdn-cgi/image',
];
