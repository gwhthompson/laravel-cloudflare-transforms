<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\CloudflareTransformsServiceProvider;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Gwhthompson\CloudflareTransforms\NullCloudflareImage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Helper to create a FilesystemAdapter with specific config for testing macros.
 *
 * @param  array<string, mixed>  $config
 */
function createTestAdapter(array $config): FilesystemAdapter
{
    $localAdapter = new LocalFilesystemAdapter(sys_get_temp_dir());
    $filesystem = new Filesystem($localAdapter, $config);

    return new FilesystemAdapter($filesystem, $localAdapter, $config);
}

describe('Storage macros with url config', function () {
    beforeEach(function () {
        // Clear global domain fallback to test disk-level config
        config(['cloudflare-transforms.domain' => null]);
        // Disable file validation for these tests
        config(['cloudflare-transforms.validate_file_exists' => false]);
    });

    describe('image() macro', function () {
        it('returns CloudflareImage when disk has url config', function () {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com']);
            $image = $adapter->image('test.jpg');

            expect($image)->toBeInstanceOf(CloudflareImage::class);
        });

        it('returns NullCloudflareImage when disk has no url config', function () {
            $adapter = createTestAdapter([]);
            $image = $adapter->image('test.jpg');

            expect($image)->toBeInstanceOf(NullCloudflareImage::class);
        });

        it('extracts domain from url config', function () {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com']);
            $image = $adapter->image('test.jpg');
            $url = $image->url();

            expect($url)->toContain('cdn.example.com');
        });

        it('handles url with path', function () {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com/storage']);
            $image = $adapter->image('test.jpg');
            $url = $image->url();

            expect($url)->toContain('cdn.example.com');
        });
    });

    describe('cloudflareUrl() macro', function () {
        it('applies transformations when disk has url config', function () {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com']);
            $url = $adapter->cloudflareUrl('test.jpg', ['width' => 300, 'height' => 200]);

            expect($url)
                ->toContain('w=300')
                ->toContain('h=200')
                ->toContain('cdn.example.com');
        });

        it('returns regular URL when disk has no url config', function () {
            $adapter = createTestAdapter([]);
            $url = $adapter->cloudflareUrl('test.jpg', ['width' => 300]);

            expect($url)->toBeString();
            expect($url)->not->toContain('w=300');
        });

        it('applies all supported options', function (array $options, string $expected) {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com']);
            $url = $adapter->cloudflareUrl('test.jpg', $options);

            expect($url)->toContain($expected);
        })->with('transform_options');

        it('validates option types', function () {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com']);
            $options = [
                'width' => '300', // string instead of int - should be ignored
                'height' => 200,
            ];

            $url = $adapter->cloudflareUrl('test.jpg', $options);

            expect($url)
                ->toContain('h=200')
                ->not->toContain('w=300');
        });

        it('returns base URL without options', function () {
            $adapter = createTestAdapter(['url' => 'https://cdn.example.com']);
            $url = $adapter->cloudflareUrl('test.jpg');

            expect($url)->toBe('https://cdn.example.com/test.jpg');
        });
    });
});

describe('Storage macros with global domain fallback', function () {
    beforeEach(function () {
        config(['cloudflare-transforms.validate_file_exists' => false]);
    });

    it('uses global domain when disk has no url config', function () {
        config(['cloudflare-transforms.domain' => 'fallback.cloudflare.com']);

        $adapter = createTestAdapter([]);
        $image = $adapter->image('test.jpg');

        expect($image)->toBeInstanceOf(CloudflareImage::class);

        $url = $image->url();
        expect($url)->toContain('fallback.cloudflare.com');
    });

    it('prefers disk url config over global domain', function () {
        config(['cloudflare-transforms.domain' => 'fallback.cloudflare.com']);

        $adapter = createTestAdapter(['url' => 'https://disk-specific.cloudflare.com']);
        $url = $adapter->image('test.jpg')->url();

        expect($url)
            ->toContain('disk-specific.cloudflare.com')
            ->not->toContain('fallback.cloudflare.com');
    });
});

describe('NullCloudflareImage behavior via macros', function () {
    beforeEach(function () {
        config(['cloudflare-transforms.domain' => null]);
        config(['cloudflare-transforms.validate_file_exists' => false]);
    });

    it('ignores all transformations', function () {
        $adapter = createTestAdapter([]);
        $image = $adapter->image('test.jpg');

        expect($image)->toBeInstanceOf(NullCloudflareImage::class);

        $url = $image
            ->width(300)
            ->height(200)
            ->format(Format::Webp)
            ->quality(Quality::High)
            ->fit(Fit::Cover)
            ->url();

        expect($url)
            ->not->toContain('w=300')
            ->not->toContain('h=200')
            ->not->toContain('f=webp')
            ->not->toContain('cdn-cgi/image');
    });

    it('returns regular storage URL', function () {
        $adapter = createTestAdapter([]);
        $image = $adapter->image('test.jpg');
        $url = $image->url();

        expect($url)->toBeString();
        expect($url)->toContain('test.jpg');
    });
});

describe('extractDomain static helper', function () {
    it('extracts domain from https url', function () {
        $domain = CloudflareTransformsServiceProvider::extractDomain(['url' => 'https://cdn.example.com']);
        expect($domain)->toBe('cdn.example.com');
    });

    it('extracts domain from http url', function () {
        $domain = CloudflareTransformsServiceProvider::extractDomain(['url' => 'http://cdn.example.com']);
        expect($domain)->toBe('cdn.example.com');
    });

    it('extracts domain from url with path', function () {
        $domain = CloudflareTransformsServiceProvider::extractDomain(['url' => 'https://cdn.example.com/storage/app']);
        expect($domain)->toBe('cdn.example.com');
    });

    it('extracts domain from url with port', function () {
        $domain = CloudflareTransformsServiceProvider::extractDomain(['url' => 'https://cdn.example.com:8080']);
        expect($domain)->toBe('cdn.example.com');
    });

    it('returns empty for missing url', function () {
        config(['cloudflare-transforms.domain' => null]);
        $domain = CloudflareTransformsServiceProvider::extractDomain([]);
        expect($domain)->toBe('');
    });

    it('returns empty for malformed url', function () {
        config(['cloudflare-transforms.domain' => null]);
        $domain = CloudflareTransformsServiceProvider::extractDomain(['url' => 'not-a-url']);
        expect($domain)->toBe('');
    });

    it('falls back to global config', function () {
        config(['cloudflare-transforms.domain' => 'global.example.com']);
        $domain = CloudflareTransformsServiceProvider::extractDomain([]);
        expect($domain)->toBe('global.example.com');
    });
});
