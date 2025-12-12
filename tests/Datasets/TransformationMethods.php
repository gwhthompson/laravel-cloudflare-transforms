<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;

dataset('transformation_methods', [
    'width' => ['width', 300],
    'height' => ['height', 200],
    'format' => ['format', Format::Webp],
    'quality_int' => ['quality', 85],
    'quality_enum' => ['quality', Quality::High],
    'blur' => ['blur', 10],
    'brightness' => ['brightness', 0.5],
    'contrast' => ['contrast', 1.2],
    'gamma' => ['gamma', 1.5],
    'rotate' => ['rotate', 90],
    'saturation' => ['saturation', 1.1],
    'sharpen' => ['sharpen', 5],
    'dpr' => ['dpr', 2.0],
    'anim' => ['anim', true],
    'background' => ['background', '#ff0000'],
    'trim' => ['trim'],
    'grayscale' => ['grayscale'],
    'optimize' => ['optimize'],
    'responsive' => ['responsive', 800],
    'thumbnail' => ['thumbnail'],
    'fit' => ['fit', Fit::Cover],
    'flip' => ['flip', Flip::Horizontal],
    'gravity' => ['gravity', Gravity::Auto],
    'metadata' => ['metadata', Metadata::None],
    'compression' => ['compression', 'fast'],
    'onerror' => ['onerror', 'redirect'],
    'segment' => ['segment', 'foreground'],
    'slowConnectionQuality' => ['slowConnectionQuality', 75],
]);

dataset('chainable_methods', [
    'single_method' => [['width' => [300]]],
    'two_methods' => [['width' => [300], 'height' => [200]]],
    'three_methods' => [['width' => [300], 'height' => [200], 'quality' => [85]]],
    'many_methods' => [['width' => [300], 'height' => [200], 'quality' => [85], 'blur' => [10], 'brightness' => [0.5]]],
]);
