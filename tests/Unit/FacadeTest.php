<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\CloudflareImageFactory;
use Gwhthompson\CloudflareTransforms\Facades\CloudflareImage as CloudflareImageFacade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.jpg', 'fake content');
    Config::set('cloudflare-transforms.domain', 'example.cloudflare.com');
});

describe('CloudflareImage Facade', function () {
    it('resolves to CloudflareImageFactory')
        ->expect(fn () => app(CloudflareImageFactory::class))
        ->toBeInstanceOf(CloudflareImageFactory::class);

    it('creates CloudflareImage via facade make method')
        ->expect(fn () => CloudflareImageFacade::make('test.jpg'))
        ->toBeInstanceOf(CloudflareImage::class);

    it('generates URL via facade', function () {
        $url = CloudflareImageFacade::make('test.jpg')->width(300)->url();
        expect($url)->toContain('w=300');
    });

    it('returns singleton instance of factory', function () {
        $factory1 = app(CloudflareImageFactory::class);
        $factory2 = app(CloudflareImageFactory::class);

        expect($factory1)->toBe($factory2);
    });
});
