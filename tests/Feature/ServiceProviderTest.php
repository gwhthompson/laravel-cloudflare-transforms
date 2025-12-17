<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\CloudflareTransformsServiceProvider;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Exceptions\FileNotFoundException;
use Gwhthompson\CloudflareTransforms\NullCloudflareImage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Helper to create a FilesystemAdapter with specific config for testing.
 *
 * @param  array<string, mixed>  $config
 */
function createAdapter(array $config): FilesystemAdapter
{
    $localAdapter = new LocalFilesystemAdapter(sys_get_temp_dir());
    $filesystem = new Filesystem($localAdapter, $config);

    return new FilesystemAdapter($filesystem, $localAdapter, $config);
}

describe('CloudflareTransformsServiceProvider', function () {
    describe('macro registration', function () {
        it('registers image macro on FilesystemAdapter', function () {
            expect(FilesystemAdapter::hasMacro('image'))->toBeTrue();
        });

        it('registers cloudflareUrl macro on FilesystemAdapter', function () {
            expect(FilesystemAdapter::hasMacro('cloudflareUrl'))->toBeTrue();
        });
    });

    describe('extractDomain helper', function () {
        it('extracts domain from url config', function () {
            $config = ['url' => 'https://cdn.example.com/storage'];
            $domain = CloudflareTransformsServiceProvider::extractDomain($config);

            expect($domain)->toBe('cdn.example.com');
        });

        it('returns empty string for config without url', function () {
            config(['cloudflare-transforms.domain' => null]);

            $config = ['driver' => 'local'];
            $domain = CloudflareTransformsServiceProvider::extractDomain($config);

            expect($domain)->toBe('');
        });

        it('falls back to global config when url not set', function () {
            config(['cloudflare-transforms.domain' => 'fallback.example.com']);

            $config = ['driver' => 'local'];
            $domain = CloudflareTransformsServiceProvider::extractDomain($config);

            expect($domain)->toBe('fallback.example.com');
        });

        it('handles malformed url config', function () {
            config(['cloudflare-transforms.domain' => null]);

            $config = ['url' => 'not-a-valid-url'];
            $domain = CloudflareTransformsServiceProvider::extractDomain($config);

            expect($domain)->toBe('');
        });
    });

    describe('Storage macros', function () {
        beforeEach(function () {
            config(['cloudflare-transforms.domain' => null]);
            config(['cloudflare-transforms.validate_file_exists' => false]);
        });

        describe('cloudflareUrl macro', function () {
            it('returns string on any disk', function () {
                $adapter = createAdapter([]);
                $url = $adapter->cloudflareUrl('test.jpg');

                expect($url)->toBeString();
            });

            it('applies transformations when url config present', function () {
                $adapter = createAdapter(['url' => 'https://cdn.test.com']);
                $url = $adapter->cloudflareUrl('test.jpg', ['width' => 300]);

                expect($url)->toContain('w=300');
                expect($url)->toContain('cdn.test.com');
            });

            it('returns regular URL when no url config', function () {
                $adapter = createAdapter([]);
                $url = $adapter->cloudflareUrl('test.jpg', ['width' => 300]);

                expect($url)->toBeString();
                expect($url)->not->toContain('w=300');
            });
        });

        describe('image macro', function () {
            it('returns CloudflareImage when url config present', function () {
                $adapter = createAdapter(['url' => 'https://cdn.test.com']);
                $image = $adapter->image('test.jpg');

                expect($image)->toBeInstanceOf(CloudflareImage::class);
            });

            it('returns NullCloudflareImage when no url config', function () {
                $adapter = createAdapter([]);
                $image = $adapter->image('test.jpg');

                expect($image)->toBeInstanceOf(NullCloudflareImage::class);
            });

            it('NullCloudflareImage returns regular URL', function () {
                $adapter = createAdapter([]);
                $image = $adapter->image('test.jpg');

                $url = $image->width(300)->url();

                expect($url)->toBeString();
                expect($url)->not->toContain('w=300');
            });
        });
    });

    describe('configuration', function () {
        it('merges package config', function () {
            expect(config('cloudflare-transforms.domain'))->toBe('example.cloudflare.com');
            expect(config('cloudflare-transforms.disk'))->toBe('public');
            expect(config('cloudflare-transforms.transform_path'))->toBe('cdn-cgi/image');
        });

        it('can override config values', function () {
            config(['cloudflare-transforms.domain' => 'custom.domain.com']);
            expect(config('cloudflare-transforms.domain'))->toBe('custom.domain.com');
        });

        it('has validate_file_exists config', function () {
            expect(config('cloudflare-transforms.validate_file_exists'))->toBeBool();
        });
    });

    describe('about command integration', function () {
        it('registers package information', function () {
            $provider = new CloudflareTransformsServiceProvider(app());
            expect(method_exists($provider, 'registerAboutCommand'))->toBeTrue();
        });

        it('displays package info in artisan about command', function () {
            $this->artisan('about')
                ->expectsOutputToContain('Cloudflare Transforms')
                ->assertSuccessful();
        });

        it('shows domain configuration in about command', function () {
            config(['cloudflare-transforms.domain' => 'test.example.com']);

            $this->artisan('about')
                ->expectsOutputToContain('test.example.com')
                ->assertSuccessful();
        });

        it('shows not configured when domain is empty', function () {
            config(['cloudflare-transforms.domain' => '']);

            $this->artisan('about')
                ->expectsOutputToContain('Not configured')
                ->assertSuccessful();
        });
    });

    describe('applyPathPrefix helper', function () {
        it('returns path unchanged when no prefix', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('photo.jpg', []);
            expect($result)->toBe('photo.jpg');
        });

        it('returns path unchanged when prefix is empty string', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('photo.jpg', ['prefix' => '']);
            expect($result)->toBe('photo.jpg');
        });

        it('prepends prefix to path', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('photo.jpg', ['prefix' => 'uploads']);
            expect($result)->toBe('uploads/photo.jpg');
        });

        it('handles prefix with trailing slash', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('photo.jpg', ['prefix' => 'uploads/']);
            expect($result)->toBe('uploads/photo.jpg');
        });

        it('handles path with leading slash', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('/photo.jpg', ['prefix' => 'uploads']);
            expect($result)->toBe('uploads/photo.jpg');
        });

        it('handles nested prefix', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('photo.jpg', ['prefix' => 'media/images']);
            expect($result)->toBe('media/images/photo.jpg');
        });

        it('handles nested path', function () {
            $result = CloudflareTransformsServiceProvider::applyPathPrefix('venues/photo.jpg', ['prefix' => 'uploads']);
            expect($result)->toBe('uploads/venues/photo.jpg');
        });
    });

    describe('scoped disk path prefix', function () {
        beforeEach(function () {
            config(['cloudflare-transforms.domain' => null]);
            config(['cloudflare-transforms.validate_file_exists' => false]);
        });

        it('includes prefix in transform URL for image macro', function () {
            $adapter = createAdapter([
                'url' => 'https://cdn.example.com',
                'prefix' => 'videos',
            ]);

            $url = $adapter->image('clip.mp4')->url();

            expect($url)->toContain('videos/clip.mp4');
        });

        it('includes prefix in transform URL for cloudflareUrl macro', function () {
            $adapter = createAdapter([
                'url' => 'https://cdn.example.com',
                'prefix' => 'videos',
            ]);

            $url = $adapter->cloudflareUrl('clip.mp4', ['width' => 300]);

            expect($url)->toContain('videos/clip.mp4');
            expect($url)->toContain('w=300');
        });

        it('handles nested prefix in transform URL', function () {
            $adapter = createAdapter([
                'url' => 'https://cdn.example.com',
                'prefix' => 'media/uploads',
            ]);

            $url = $adapter->image('photo.jpg')->width(400)->url();

            expect($url)->toContain('media/uploads/photo.jpg');
        });
    });
});

describe('Package integration', function () {
    it('works end-to-end with CloudflareImage', function () {
        Storage::fake('public');
        Storage::disk('public')->put('test.jpg', 'fake content');

        $image = CloudflareImage::make('test.jpg');
        $url = $image->width(300)->height(200)->format(Format::Webp)->url();

        expect($url)
            ->toStartWith('https://')
            ->toContain('w=300')
            ->toContain('h=200')
            ->toContain('f=webp')
            ->toEndWith('/test.jpg');
    });

    it('works with Storage macros - NullCloudflareImage', function () {
        config(['cloudflare-transforms.domain' => null]);
        config(['cloudflare-transforms.validate_file_exists' => false]);

        $adapter = createAdapter([]);
        $image = $adapter->image('test.jpg');

        expect($image)->toBeInstanceOf(NullCloudflareImage::class);

        $url = $image->width(300)->height(200)->url();
        expect($url)->not->toContain('w=300');
        expect($url)->not->toContain('h=200');
    });

    it('works with url config end-to-end', function () {
        config(['cloudflare-transforms.validate_file_exists' => false]);

        $adapter = createAdapter(['url' => 'https://cdn.e2e-test.com']);
        $image = $adapter->image('test.jpg');

        expect($image)->toBeInstanceOf(CloudflareImage::class);

        $url = $image->width(300)->height(200)->url();
        expect($url)
            ->toContain('cdn.e2e-test.com')
            ->toContain('w=300')
            ->toContain('h=200');
    });
});

describe('file validation in macros', function () {
    it('throws FileNotFoundException from image macro when file missing', function () {
        Storage::fake('cloudflare');
        config(['filesystems.disks.cloudflare.url' => 'https://cdn.example.com']);
        config(['cloudflare-transforms.validate_file_exists' => true]);

        expect(fn () => Storage::disk('cloudflare')->image('nonexistent.jpg'))
            ->toThrow(FileNotFoundException::class);
    });

    it('throws FileNotFoundException from cloudflareUrl macro when file missing', function () {
        Storage::fake('cloudflare');
        config(['filesystems.disks.cloudflare.url' => 'https://cdn.example.com']);
        config(['cloudflare-transforms.validate_file_exists' => true]);

        expect(fn () => Storage::disk('cloudflare')->cloudflareUrl('nonexistent.jpg'))
            ->toThrow(FileNotFoundException::class);
    });

    it('does not throw when validate_file_exists is false', function () {
        Storage::fake('cloudflare');
        config(['filesystems.disks.cloudflare.url' => 'https://cdn.example.com']);
        config(['cloudflare-transforms.validate_file_exists' => false]);

        $image = Storage::disk('cloudflare')->image('nonexistent.jpg');
        expect($image)->toBeInstanceOf(CloudflareImage::class);
    });
});
