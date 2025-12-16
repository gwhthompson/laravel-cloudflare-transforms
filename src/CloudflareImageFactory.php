<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

/**
 * Factory class for creating CloudflareImage instances.
 *
 * This class is resolved via the Laravel service container for Facade support.
 * It provides a non-static entry point to the CloudflareImage builder pattern.
 */
final class CloudflareImageFactory
{
    /**
     * Create a new CloudflareImage instance.
     *
     * @param  string  $path  The image path (relative to the configured disk)
     * @param  string|null  $domain  Cloudflare domain (defaults to config value)
     * @param  string|null  $disk  Storage disk name (defaults to config value)
     * @param  string|null  $transformPath  Transform URL path (defaults to 'cdn-cgi/image')
     * @param  bool|null  $validateExists  Whether to validate file existence (defaults to config value)
     */
    public function make(
        string $path,
        ?string $domain = null,
        ?string $disk = null,
        ?string $transformPath = null,
        ?bool $validateExists = null,
    ): CloudflareImage {
        return CloudflareImage::make($path, $domain, $disk, $transformPath, $validateExists);
    }
}
