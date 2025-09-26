<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Quality;

dataset('transform_options', [
    'width_only' => [
        ['width' => 300],
        'w=300'
    ],
    'width_and_height' => [
        ['width' => 300, 'height' => 200],
        'w=300,h=200'
    ],
    'with_format' => [
        ['width' => 300, 'format' => Format::Webp],
        'w=300,f=webp'
    ],
    'with_quality' => [
        ['width' => 300, 'quality' => 85],
        'w=300,q=85'
    ],
    'with_quality_enum' => [
        ['width' => 300, 'quality' => Quality::High],
        'w=300,q=high'
    ],
    'with_fit' => [
        ['width' => 300, 'height' => 200, 'fit' => Fit::Cover],
        'w=300,h=200,fit=cover'
    ],
    'complex_transform' => [
        ['width' => 300, 'height' => 200, 'format' => Format::Avif, 'quality' => Quality::MediumHigh, 'fit' => Fit::Crop],
        'w=300,h=200,f=avif,q=medium-high,fit=crop'
    ],
]);

dataset('url_prefixes', [
    'no_prefix' => [null, 'test.jpg'],
    'with_prefix' => ['uploads/', 'uploads/test.jpg'],
    'nested_prefix' => ['assets/images/', 'assets/images/test.jpg'],
]);