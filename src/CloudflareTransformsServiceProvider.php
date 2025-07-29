<?php

namespace Gwhthompson\CloudflareTransforms;

use Illuminate\Support\ServiceProvider;

class CloudflareTransformsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/config/cloudflare-transforms.php' => $this->app->configPath('cloudflare-transforms.php'),
        ], 'cloudflare-transforms-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/cloudflare-transforms.php',
            'cloudflare-transforms'
        );
    }
}
