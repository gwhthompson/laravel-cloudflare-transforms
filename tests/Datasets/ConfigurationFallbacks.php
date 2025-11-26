<?php

declare(strict_types=1);

dataset('config_fallback_scenarios', [
    'app_url_fallback' => [
        ['cloudflare-transforms.domain' => null, 'app.url' => 'https://myapp.com'],
        'myapp.com',
    ],
    'localhost_fallback' => [
        ['cloudflare-transforms.domain' => null, 'app.url' => null],
        'localhost',
    ],
]);
