<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Exceptions\FileNotFoundException;
use Gwhthompson\CloudflareTransforms\Exceptions\InvalidTransformParameterException;

dataset('invalid_paths', [
    'empty_string' => ['', 'Invalid path', InvalidTransformParameterException::class],
    'path_traversal' => ['../test.jpg', 'Invalid path', InvalidTransformParameterException::class],
    'nonexistent_file' => ['nonexistent.jpg', 'File does not exist', FileNotFoundException::class],
]);

dataset('transformation_combinations', [
    'basic_resize' => [
        ['width' => 300, 'height' => 200],
        'w=300,h=200',
    ],
    'resize_with_format' => [
        ['width' => 300],
        'w=300',
    ],
    'full_transform' => [
        ['width' => 300, 'height' => 200, 'quality' => 85],
        'w=300,h=200,q=85',
    ],
]);

dataset('special_encoding_cases', [
    'background_hash' => ['#ff0000', 'background=%23ff0000'],
    'background_rgb' => ['rgb(255,0,0)', 'background=rgb%28255%2C0%2C0%29'],
    'anim_true' => [true, 'anim=true'],
    'anim_false' => [false, 'anim=false'],
]);
