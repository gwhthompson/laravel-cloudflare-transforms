<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Enums;

enum Format: string
{
    case Auto = 'auto';
    case Avif = 'avif';
    case BaselineJpeg = 'baseline-jpeg';
    case Jpeg = 'jpeg';
    case Json = 'json';
    case Webp = 'webp';
}
