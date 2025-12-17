<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

use Composer\InstalledVersions;
use Gwhthompson\CloudflareTransforms\Contracts\CloudflareImageContract;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Gwhthompson\CloudflareTransforms\Exceptions\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Override;

class CloudflareTransformsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/cloudflare-transforms.php' => $this->app->configPath('cloudflare-transforms.php'),
        ], 'cloudflare-transforms-config');

        $this->publishes([
            __DIR__.'/resources/views' => $this->app->resourcePath('views/vendor/cloudflare'),
        ], 'cloudflare-transforms-views');

        $this->registerStorageMacros();
        $this->registerBladeComponents();
        $this->registerAboutCommand();
    }

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/cloudflare-transforms.php',
            'cloudflare-transforms'
        );

        // Bind factory for Facade support
        $this->app->singleton(CloudflareImageFactory::class);
    }

    /**
     * Register Storage macros for transformation support on all FilesystemAdapter instances.
     *
     * Works with any disk driver (s3, local, ftp, etc.) that has a 'url' config
     * pointing to a Cloudflare-proxied domain with Image Transformations enabled.
     */
    protected function registerStorageMacros(): void
    {
        FilesystemAdapter::macro('image', function (string $path): CloudflareImageContract {
            /** @var FilesystemAdapter $this */
            $config = $this->getConfig();
            $domain = CloudflareTransformsServiceProvider::extractDomain($config);

            if ($domain === '') {
                return new NullCloudflareImage($this->url($path));
            }

            // Apply path prefix if configured (for scoped disks)
            $fullPath = CloudflareTransformsServiceProvider::applyPathPrefix($path, $config);

            // Validate on THIS disk (not config default)
            $validateExists = Config::get('cloudflare-transforms.validate_file_exists', true);
            if ($validateExists && ! $this->exists($path)) {
                throw FileNotFoundException::forPath($path);
            }

            return CloudflareImage::make($fullPath, $domain, validateExists: false);
        });

        FilesystemAdapter::macro('cloudflareUrl', function (string $path, array $options = []): string {
            /** @var FilesystemAdapter $this */
            $config = $this->getConfig();
            $domain = CloudflareTransformsServiceProvider::extractDomain($config);

            if ($domain === '') {
                return $this->url($path);
            }

            // Apply path prefix if configured (for scoped disks)
            $fullPath = CloudflareTransformsServiceProvider::applyPathPrefix($path, $config);

            // Validate on THIS disk (not config default)
            $validateExists = Config::get('cloudflare-transforms.validate_file_exists', true);
            if ($validateExists && ! $this->exists($path)) {
                throw FileNotFoundException::forPath($path);
            }

            $cloudflareImage = CloudflareImage::make($fullPath, $domain, validateExists: false);

            // Apply transformations from options array (proper fluent pattern - reassign return values)
            if (isset($options['width']) && is_int($options['width'])) {
                $cloudflareImage = $cloudflareImage->width($options['width']);
            }

            if (isset($options['height']) && is_int($options['height'])) {
                $cloudflareImage = $cloudflareImage->height($options['height']);
            }

            if (isset($options['format']) && $options['format'] instanceof Format) {
                $cloudflareImage = $cloudflareImage->format($options['format']);
            }

            if (isset($options['quality']) && (is_int($options['quality']) || $options['quality'] instanceof Quality)) {
                $cloudflareImage = $cloudflareImage->quality($options['quality']);
            }

            if (isset($options['fit']) && $options['fit'] instanceof Fit) {
                $cloudflareImage = $cloudflareImage->fit($options['fit']);
            }

            return $cloudflareImage->url();
        });
    }

    /** Register Blade views and components for the package. */
    protected function registerBladeComponents(): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'cloudflare');

        Blade::componentNamespace(
            'Gwhthompson\\CloudflareTransforms\\View\\Components',
            'cloudflare'
        );
    }

    /**
     * Apply path prefix from disk config (for scoped disks).
     *
     * @param  array<array-key, mixed>  $config
     */
    public static function applyPathPrefix(string $path, array $config): string
    {
        $prefix = $config['prefix'] ?? null;

        if (! is_string($prefix) || $prefix === '') {
            return $path;
        }

        return rtrim($prefix, '/').'/'.ltrim($path, '/');
    }

    /**
     * Extract Cloudflare domain from disk config or package config fallback.
     *
     * @param  array<array-key, mixed>  $config
     */
    public static function extractDomain(array $config): string
    {
        $url = is_string($config['url'] ?? null) ? $config['url'] : '';
        $domain = parse_url($url, PHP_URL_HOST);

        // Type-safe handling for parse_url which returns string|false|null
        if ($domain === false || $domain === null) {
            $fallback = Config::get('cloudflare-transforms.domain');
            $domain = is_string($fallback) ? $fallback : '';
        }

        return $domain;
    }

    /** Register package information for the about command. */
    protected function registerAboutCommand(): void
    {
        AboutCommand::add('Cloudflare Transforms', function (): array {
            $domain = Config::get('cloudflare-transforms.domain');
            $transformPath = Config::get('cloudflare-transforms.transform_path', 'cdn-cgi/image');
            $validateExists = Config::get('cloudflare-transforms.validate_file_exists', true);

            return [
                'Version' => InstalledVersions::getPrettyVersion('gwhthompson/laravel-cloudflare-transforms') ?? 'unknown',
                'Domain' => is_string($domain) && $domain !== '' ? $domain : '<comment>Not configured</comment>',
                'Transform Path' => is_string($transformPath) ? $transformPath : 'cdn-cgi/image',
                'File Validation' => (is_bool($validateExists) ? $validateExists : true) ? 'Enabled' : 'Disabled',
            ];
        });
    }
}
