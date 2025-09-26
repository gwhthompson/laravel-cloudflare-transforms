<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Tests;

use Gwhthompson\CloudflareTransforms\CloudflareTransformsServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            CloudflareTransformsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cloudflare-transforms.domain', 'example.cloudflare.com');
        $app['config']->set('cloudflare-transforms.disk', 'public');
        $app['config']->set('cloudflare-transforms.transform_path', 'cdn-cgi/image');
        $app['config']->set('filesystems.default', 'public');
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ]);
    }
}