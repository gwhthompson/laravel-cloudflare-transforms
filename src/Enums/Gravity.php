<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

/**
 * Focal point for cropping when used with fit=cover or fit=crop.
 * Face uses AI to detect faces for optimal cropping.
 */
enum Gravity: string
{
    case Auto = 'auto';
    case Bottom = 'bottom';
    case Face = 'face';
    case Left = 'left';
    case Right = 'right';
    case Top = 'top';
}
