<?php

declare(strict_types=1);

dataset('validation_ranges', [
    'width_zero' => ['width', 0, 'Width must be 1-12,000'],
    'height_negative' => ['height', -1, 'Height must be 1-12,000'],
    'blur_over_limit' => ['blur', 251, 'Blur must be 1-250'],
    'brightness_over_limit' => ['brightness', 2.5, 'Brightness must be 0-2'],
    'contrast_over_limit' => ['contrast', 2.5, 'Contrast must be 0-2'],
    'gamma_over_limit' => ['gamma', 2.5, 'Gamma must be 0-2'],
    'rotate_over_limit' => ['rotate', 45, 'Rotation must be 90, 180, or 270'],
    'saturation_over_limit' => ['saturation', 2.5, 'Saturation must be 0-2'],
    'sharpen_over_limit' => ['sharpen', 15, 'Sharpen must be 0-10'],
    'zoom_over_limit' => ['zoom', 1.5, 'Zoom must be 0-1'],
]);

dataset('trim_border_validation', [
    'tolerance_over_255' => [256, 'Tolerance must be between 0-255'],
    'keep_negative' => [-1, 'Keep must be 0 or greater'],
]);

dataset('gravity_coordinates', [
    'valid_center' => ['0.5x0.5', true],
    'valid_corners' => ['0x1', true],
    'valid_decimals' => ['0.33x0.67', true],
    'invalid_format' => ['invalid', false],
    'invalid_over_1' => ['1.5x0.5', false],
    'invalid_negative' => ['-0.1x0.5', false],
]);