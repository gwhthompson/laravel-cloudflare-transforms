<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

enum Quality: string
{
    case High = 'high';
    case Low = 'low';
    case MediumHigh = 'medium-high';
    case MediumLow = 'medium-low';
}
