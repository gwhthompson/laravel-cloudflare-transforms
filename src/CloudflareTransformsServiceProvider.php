<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

use Override;
use Aws\S3\S3Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class CloudflareTransformsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/cloudflare-transforms.php' => $this->app->configPath('cloudflare-transforms.php'),
        ], 'cloudflare-transforms-config');

        $this->registerCloudflareDriver();
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

    /** Register the cloudflare-s3 driver for S3-compatible storage with Cloudflare URLs. */
    protected function registerCloudflareDriver(): void
    {
        Storage::extend('cloudflare-s3', function (Application $application, array $config): CloudflareFilesystemAdapter {
            // Create S3 client (works with Backblaze B2 and other S3-compatible services)
            $s3Client = new S3Client([
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region' => $config['region'],
                'version' => 'latest',
                'endpoint' => $config['endpoint'] ?? null,
                'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
            ]);

            $awsS3V3Adapter = new AwsS3V3Adapter($s3Client, $config['bucket']);
            $filesystem = new Filesystem($awsS3V3Adapter, $config);

            // Return our custom adapter that handles Cloudflare URLs automatically
            return new CloudflareFilesystemAdapter($filesystem, $awsS3V3Adapter, $config);
        });
    }

    /** Register Storage macros for transformation support on all FilesystemAdapter instances. */
    protected function registerStorageMacros(): void
    {
        // Add transformation methods to all FilesystemAdapter instances
        FilesystemAdapter::macro('cloudflareUrl', function (string $path, array $options = []) {
            if ($this instanceof CloudflareFilesystemAdapter) {
                return $this->transformedUrl($path, $options);
            }

            // Fallback to regular URL for non-Cloudflare disks
            return $this->url($path);
        });

        FilesystemAdapter::macro('image', function (string $path): CloudflareImage|NullCloudflareImage {
            if ($this instanceof CloudflareFilesystemAdapter) {
                return $this->image($path);
            }

            // Return a null object that provides same API but returns regular URLs
            return new NullCloudflareImage($this->url($path));
        });
    }

    /** Register package information for the about command. */
    protected function registerAboutCommand(): void
    {
        AboutCommand::add('Cloudflare Transforms', function (): array {
            $composerFile = __DIR__.'/../composer.json';
            $composerData = [];

            if (is_readable($composerFile)) {
                $content = file_get_contents($composerFile);
                if (is_string($content)) {
                    $decoded = json_decode($content, true);
                    if (is_array($decoded)) {
                        $composerData = $decoded;
                    }
                }
            }

            return [
                'Version' => $composerData['version'] ?? 'unknown',
                'Domain' => config('cloudflare-transforms.domain') ?: '<comment>Not configured</comment>',
                'Transform Path' => config('cloudflare-transforms.transform_path', 'cdn-cgi/image'),
            ];
        });
    }
}
