<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

/**
 * How to resize the image within the given width and height dimensions.
 * All resizing modes preserve aspect ratio.
 */
enum Fit: string
{
    case Contain = 'contain';
    case Cover = 'cover';
    case Crop = 'crop';
    case Pad = 'pad';
    case ScaleDown = 'scale-down';
    case Squeeze = 'squeeze';
}
