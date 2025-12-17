<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\CloudflareImageFactory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.jpg', 'fake content');

    Config::set('cloudflare-transforms.domain', 'example.cloudflare.com');
    Config::set('cloudflare-transforms.disk', 'public');
    Config::set('cloudflare-transforms.transform_path', 'cdn-cgi/image');
});

describe('CloudflareImageFactory', function () {
    it('can be instantiated')
        ->expect(fn () => new CloudflareImageFactory)
        ->toBeInstanceOf(CloudflareImageFactory::class);

    it('make() returns CloudflareImage instance')
        ->expect(fn () => (new CloudflareImageFactory)->make('test.jpg'))
        ->toBeInstanceOf(CloudflareImage::class);

    it('make() passes path correctly', function () {
        $factory = new CloudflareImageFactory;
        $image = $factory->make('test.jpg');

        expect($image->url())->toContain('test.jpg');
    });

    it('make() passes domain override', function () {
        $factory = new CloudflareImageFactory;
        $image = $factory->make('test.jpg', domain: 'custom.example.com');

        expect($image->width(100)->url())
            ->toBe('https://custom.example.com/cdn-cgi/image/w=100/test.jpg');
    });

    it('make() passes transformPath override', function () {
        $factory = new CloudflareImageFactory;
        $image = $factory->make('test.jpg', transformPath: 'image');

        expect($image->width(100)->url())
            ->toBe('https://example.cloudflare.com/image/w=100/test.jpg');
    });

    it('make() passes validateExists override', function () {
        $factory = new CloudflareImageFactory;

        // Should not throw when validateExists is false
        $image = $factory->make('nonexistent.jpg', validateExists: false);

        expect($image)->toBeInstanceOf(CloudflareImage::class);
    });
});
