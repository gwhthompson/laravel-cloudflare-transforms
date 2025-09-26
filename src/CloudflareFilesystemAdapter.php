<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

use Override;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\FilesystemAdapter as FlysystemAdapter;
use League\Flysystem\FilesystemOperator;

class CloudflareFilesystemAdapter extends FilesystemAdapter
{
    protected bool $autoTransform;

    protected string $cloudflareDomain;

    /** @param array<string, mixed> $config */
    public function __construct(
        FilesystemOperator $driver,
        FlysystemAdapter $adapter,
        array $config
    ) {
        parent::__construct($driver, $adapter, $config);

        $cloudflareDomain = $config['cloudflare_domain'] ?? config('cloudflare-transforms.domain');
        $this->cloudflareDomain = is_string($cloudflareDomain) ? $cloudflareDomain : '';

        $autoTransform = $config['auto_transform'] ?? config('cloudflare-transforms.auto_transform.enabled', true);
        $this->autoTransform = is_bool($autoTransform) ? $autoTransform : true;
    }

    /** Create a CloudflareImage builder for fluent transformation. */
    public function image(string $path): CloudflareImage
    {
        return CloudflareImage::make($path, $this->cloudflareDomain);
    }

    /**
     * Get a transformed URL using Cloudflare Image API.
     *
     * @param  array<string, mixed>  $options
     */
    public function transformedUrl(string $path, array $options = []): string
    {
        $cloudflareImage = CloudflareImage::make($path, $this->cloudflareDomain);

        // Apply transformations from options array
        if (isset($options['width']) && is_int($options['width'])) {
            $cloudflareImage->width($options['width']);
        }

        if (isset($options['height']) && is_int($options['height'])) {
            $cloudflareImage->height($options['height']);
        }

        if (isset($options['format']) && $options['format'] instanceof Format) {
            $cloudflareImage->format($options['format']);
        }

        if (isset($options['quality']) && (is_int($options['quality']) || $options['quality'] instanceof Quality)) {
            $cloudflareImage->quality($options['quality']);
        }

        if (isset($options['fit']) && $options['fit'] instanceof Fit) {
            $cloudflareImage->fit($options['fit']);
        }

        return $cloudflareImage->url();
    }

    /**
     * Get the URL for the file at the given path.
     * Automatically returns Cloudflare URL.
     */
    #[Override]
    public function url($path): string
    {
        if (isset($this->config['prefix'])) {
            $path = $this->concatPathToUrl($this->config['prefix'], $path);
        }

        return "https://{$this->cloudflareDomain}/{$path}";
    }
}
