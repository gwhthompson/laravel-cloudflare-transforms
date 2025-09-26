<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

/**
 * Controls amount of invisible EXIF metadata to preserve.
 * Default for JPEG is Copyright. WebP and PNG always discard metadata.
 */
enum Metadata: string
{
    case Copyright = 'copyright';
    case Keep = 'keep';
    case None = 'none';
}
