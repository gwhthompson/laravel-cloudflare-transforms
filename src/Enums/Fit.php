<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

enum Fit: string
{
    case Contain = 'contain';
    case Cover = 'cover';
    case Crop = 'crop';
    case Pad = 'pad';
    case ScaleDown = 'scale-down';
    case Squeeze = 'squeeze';
}
