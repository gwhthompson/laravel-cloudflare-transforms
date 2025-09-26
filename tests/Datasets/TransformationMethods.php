<?php

declare(strict_types=1);

dataset('transformation_methods', [
    'width' => ['width', 300],
    'height' => ['height', 200],
    'format' => ['format', 'webp'],
    'quality' => ['quality', 85],
    'blur' => ['blur', 10],
    'brightness' => ['brightness', 0.5],
    'contrast' => ['contrast', 1.2],
    'gamma' => ['gamma', 1.5],
    'rotate' => ['rotate', 90],
    'saturation' => ['saturation', 1.1],
    'sharpen' => ['sharpen', 5],
    'zoom' => ['zoom', 2],
    'anim' => ['anim', true],
    'background' => ['background', '#ff0000'],
    'trim' => ['trim'],
    'grayscale' => ['grayscale'],
    'optimize' => ['optimize'],
    'responsive' => ['responsive'],
    'thumbnail' => ['thumbnail'],
]);

dataset('chainable_methods', [
    'single_method' => [['width' => [300]]],
    'two_methods' => [['width' => [300], 'height' => [200]]],
    'three_methods' => [['width' => [300], 'height' => [200], 'quality' => [85]]],
    'many_methods' => [['width' => [300], 'height' => [200], 'quality' => [85], 'blur' => [10], 'brightness' => [0.5]]],
]);