<?php

namespace Gwhthompson\CloudflareTransforms\Enums;

enum Flip: string
{
    case Both = 'hv';
    case Horizontal = 'h';
    case Vertical = 'v';
}
