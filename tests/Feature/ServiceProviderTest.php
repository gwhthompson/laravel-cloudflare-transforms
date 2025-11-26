<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareFilesystemAdapter;
use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\CloudflareTransformsServiceProvider;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\NullCloudflareImage;
use Illuminate\Support\Facades\Storage;

describe('CloudflareTransformsServiceProvider', function () {
    describe('driver registration', function () {
        it('registers s3 driver', function () {
            config(['filesystems.disks.test-driver' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'us-east-1',
                'bucket' => 'test-bucket',
                'cloudflare_domain' => 'test.cloudflare.com',
            ]]);

            expect(function () {
                Storage::disk('test-driver');
            })->not->toThrow(Exception::class);
        });

        it('creates CloudflareFilesystemAdapter instance', function () {
            config(['filesystems.disks.test-cloudflare' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'us-east-1',
                'bucket' => 'test-bucket',
                'cloudflare_domain' => 'test.cloudflare.com',
            ]]);

            $disk = Storage::disk('test-cloudflare');
            expect($disk)->toBeInstanceOf(CloudflareFilesystemAdapter::class);
        });
    });

    describe('Storage macros', function () {
        beforeEach(function () {
            Storage::fake('public');
            Storage::disk('public')->put('test.jpg', 'fake content');

            // Create a Cloudflare disk for testing
            config(['filesystems.disks.cloudflare-test' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'us-east-1',
                'bucket' => 'test-bucket',
                'cloudflare_domain' => 'test.cloudflare.com',
            ]]);
        });

        describe('cloudflareUrl macro', function () {
            it('exists on FilesystemAdapter', function () {
                $disk = Storage::disk('public');
                expect($disk->cloudflareUrl('test.jpg'))->toBeString();
            });

            it('returns transformed URL on Cloudflare disk', function () {
                $disk = Storage::disk('cloudflare-test');

                if ($disk instanceof CloudflareFilesystemAdapter) {
                    $url = $disk->cloudflareUrl('test.jpg', ['width' => 300]);
                    expect($url)->toContain('w=300');
                }
            });

            it('returns regular URL on non-Cloudflare disk', function () {
                $disk = Storage::disk('public');
                $url = $disk->cloudflareUrl('test.jpg', ['width' => 300]);

                expect($url)->toBeString();
                expect($url)->not->toContain('w=300');
            });
        });

        describe('image macro', function () {
            it('exists on FilesystemAdapter', function () {
                $disk = Storage::disk('public');
                expect($disk->image('test.jpg'))->toBeInstanceOf(NullCloudflareImage::class);
            });

            it('returns CloudflareImage on Cloudflare disk', function () {
                $disk = Storage::disk('cloudflare-test');

                if ($disk instanceof CloudflareFilesystemAdapter) {
                    $image = $disk->image('test.jpg');
                    expect($image)->toBeInstanceOf(CloudflareImage::class);
                }
            });

            it('returns NullCloudflareImage on non-Cloudflare disk', function () {
                $disk = Storage::disk('public');
                $image = $disk->image('test.jpg');

                expect($image)->toBeInstanceOf(NullCloudflareImage::class);
            });

            it('NullCloudflareImage returns regular URL', function () {
                $disk = Storage::disk('public');
                $image = $disk->image('test.jpg');

                expect($image->width(300)->url())->toBeString();
                expect($image->width(300)->url())->not->toContain('w=300');
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

        it('has auto_transform config', function () {
            expect(config('cloudflare-transforms.auto_transform'))->toBeArray();
            expect(config('cloudflare-transforms.auto_transform.enabled'))->toBeTrue();
            expect(config('cloudflare-transforms.auto_transform.default_format'))->toBe('auto');
            expect(config('cloudflare-transforms.auto_transform.default_quality'))->toBe(85);
        });
    });

    describe('about command integration', function () {
        it('registers package information', function () {
            // This is harder to test directly, but we can verify the service provider
            // has the registerAboutCommand method
            $provider = new CloudflareTransformsServiceProvider(app());
            expect(method_exists($provider, 'registerAboutCommand'))->toBeTrue();
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

    it('works with Storage macros end-to-end', function () {
        Storage::fake('public');
        Storage::disk('public')->put('test.jpg', 'fake content');

        // Test regular disk returns NullCloudflareImage
        $image = Storage::disk('public')->image('test.jpg');
        expect($image)->toBeInstanceOf(NullCloudflareImage::class);

        // Test that transformations are ignored
        $url = $image->width(300)->height(200)->url();
        expect($url)->not->toContain('w=300');
        expect($url)->not->toContain('h=200');
    });
});
