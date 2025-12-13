<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

use Gwhthompson\CloudflareTransforms\Contracts\CloudflareImageContract;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use JsonException;
use Override;

class CloudflareTransformsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/cloudflare-transforms.php' => $this->app->configPath('cloudflare-transforms.php'),
        ], 'cloudflare-transforms-config');

        $this->registerStorageMacros();
        $this->registerAboutCommand();
    }

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/cloudflare-transforms.php',
            'cloudflare-transforms'
        );
    }

    /**
     * Register Storage macros for transformation support on all FilesystemAdapter instances.
     *
     * Uses Laravel's built-in S3 driver with the standard 'url' config for CDN domains.
     * No driver override needed - just adds image transformation methods.
     */
    protected function registerStorageMacros(): void
    {
        FilesystemAdapter::macro('image', function (string $path): CloudflareImageContract {
            /** @var FilesystemAdapter $this */
            $domain = CloudflareTransformsServiceProvider::extractDomain($this->getConfig());

            if ($domain === '') {
                return new NullCloudflareImage($this->url($path));
            }

            return CloudflareImage::make($path, $domain);
        });

        FilesystemAdapter::macro('cloudflareUrl', function (string $path, array $options = []): string {
            /** @var FilesystemAdapter $this */
            $domain = CloudflareTransformsServiceProvider::extractDomain($this->getConfig());

            if ($domain === '') {
                return $this->url($path);
            }

            $cloudflareImage = CloudflareImage::make($path, $domain);

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
        });
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
            $composerFile = __DIR__.'/../composer.json';

            // Modern PHP 8.4 JSON handling with JSON_THROW_ON_ERROR
            try {
                /** @var array{version?: string} $composerData */
                $composerData = json_decode(
                    (string) file_get_contents($composerFile),
                    associative: true,
                    flags: JSON_THROW_ON_ERROR
                );
            } catch (JsonException) {
                $composerData = [];
            }

            $domain = Config::get('cloudflare-transforms.domain');
            $transformPath = Config::get('cloudflare-transforms.transform_path', 'cdn-cgi/image');
            $validateExists = Config::get('cloudflare-transforms.validate_file_exists', true);

            return [
                'Version' => $composerData['version'] ?? 'unknown',
                'Domain' => is_string($domain) && $domain !== '' ? $domain : '<comment>Not configured</comment>',
                'Transform Path' => is_string($transformPath) ? $transformPath : 'cdn-cgi/image',
                'File Validation' => (is_bool($validateExists) ? $validateExists : true) ? 'Enabled' : 'Disabled',
            ];
        });
    }
}
