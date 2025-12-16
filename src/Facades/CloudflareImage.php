<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Facades;

use Gwhthompson\CloudflareTransforms\CloudflareImageFactory;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for creating CloudflareImage instances.
 *
 * @method static \Gwhthompson\CloudflareTransforms\CloudflareImage make(string $path, ?string $domain = null, ?string $disk = null, ?string $transformPath = null, ?bool $validateExists = null)
 *
 * @see CloudflareImageFactory
 */
class CloudflareImage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CloudflareImageFactory::class;
    }
}
