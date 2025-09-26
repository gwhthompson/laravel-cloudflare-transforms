<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

/**
 * Null object pattern for CloudflareImage when used on non-Cloudflare disks.
 * Provides the same API but returns regular URLs without transformations.
 */
class NullCloudflareImage
{
    private string $originalUrl;

    public function __construct(string $originalUrl)
    {
        $this->originalUrl = $originalUrl;
    }

    /**
     * Return the original URL for any transformation attempt.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): self
    {
        // All transformation methods return self for fluent interface
        return $this;
    }

    /** Return the original URL when cast to string or url() is called. */
    public function __toString(): string
    {
        return $this->originalUrl;
    }

    public function url(): string
    {
        return $this->originalUrl;
    }
}
