<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

/**
 * Flip the image horizontally, vertically, or both.
 * Flipping is performed before rotation.
 */
enum Flip: string
{
    case Both = 'hv';
    case Horizontal = 'h';
    case Vertical = 'v';
}
