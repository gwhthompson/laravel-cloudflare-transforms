<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

enum Metadata: string
{
    case Copyright = 'copyright';
    case Keep = 'keep';
    case None = 'none';
}
