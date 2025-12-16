<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Exceptions;

/**
 * Exception thrown when package configuration is missing or invalid.
 *
 * This includes missing domain configuration, invalid disk settings, etc.
 */
class ConfigurationException extends CloudflareTransformException
{
    /** Create an exception for missing domain configuration. */
    public static function missingDomain(): self
    {
        return new self(
            'No Cloudflare domain configured. Set CLOUDFLARE_TRANSFORMS_DOMAIN in your .env '
            .'or configure the "url" option on your storage disk.'
        );
    }

    /** Create an exception for an invalid domain. */
    public static function invalidDomain(string $domain): self
    {
        return new self(sprintf('Invalid Cloudflare domain: %s', $domain));
    }
}
