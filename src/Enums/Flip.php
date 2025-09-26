<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

enum Flip: string
{
    case Both = 'hv';
    case Horizontal = 'h';
    case Vertical = 'v';
}
