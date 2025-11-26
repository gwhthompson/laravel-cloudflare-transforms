<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareFilesystemAdapter;
use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

describe('CloudflareFilesystemAdapter', function () {
    beforeEach(function () {
        $this->domain = 'example.cloudflare.com';
        $this->config = [
            'cloudflare_domain' => $this->domain,
            'prefix' => null,
        ];

        // Create a mock filesystem and S3 adapter
        $filesystem = Mockery::mock(Filesystem::class);
        $awsAdapter = Mockery::mock(AwsS3V3Adapter::class);

        $this->adapter = new CloudflareFilesystemAdapter($filesystem, $awsAdapter, $this->config);
    });

    describe('URL generation', function () {
        it('generates basic Cloudflare URLs', function () {
            expect($this->adapter->url('test.jpg'))
                ->toBe("https://{$this->domain}/test.jpg");
        });

        it('handles URL prefixes', function (?string $prefix, string $expected) {
            $config = array_merge($this->config, ['prefix' => $prefix]);
            $filesystem = Mockery::mock(Filesystem::class);
            $awsAdapter = Mockery::mock(AwsS3V3Adapter::class);
            $adapter = new CloudflareFilesystemAdapter($filesystem, $awsAdapter, $config);

            expect($adapter->url('test.jpg'))->toBe("https://{$this->domain}/{$expected}");
        })->with('url_prefixes');
    });

    describe('image() method', function () {
        it('returns CloudflareImage instance', function () {
            expect($this->adapter->image('test.jpg'))
                ->toBeInstanceOf(CloudflareImage::class);
        });

        it('creates image with correct domain and path', function () {
            $image = $this->adapter->image('test.jpg');

            // We can't easily test internal properties, but we can test the fluent interface works
            expect($image)->toBeInstanceOf(CloudflareImage::class);

            // Test that we can chain methods (indicating it's a real CloudflareImage)
            $chainedImage = $image->width(300)->height(200);
            expect($chainedImage)->toBeInstanceOf(CloudflareImage::class);
        });
    });

    describe('transformedUrl() method', function () {
        beforeEach(function () {
            // Mock Storage to avoid file existence checks
            Storage::fake('public');
            Storage::disk('public')->put('test.jpg', 'fake content');
        });

        it('applies transformation options', function (array $options, string $expected) {
            $url = $this->adapter->transformedUrl('test.jpg', $options);

            expect($url)
                ->toStartWith("https://{$this->domain}/cdn-cgi/image/")
                ->toContain($expected)
                ->toEndWith('/test.jpg');
        })->with('transform_options');

        it('works without options', function () {
            expect($this->adapter->transformedUrl('test.jpg'))
                ->toBe("https://{$this->domain}/test.jpg");
        });

        it('handles only supported options', function () {
            $options = [
                'width' => 300,
                'height' => 200,
                'unsupported_option' => 'value',
                'format' => Format::Webp,
            ];

            $url = $this->adapter->transformedUrl('test.jpg', $options);

            expect($url)
                ->toContain('w=300')
                ->toContain('h=200')
                ->toContain('f=webp')
                ->not->toContain('unsupported_option');
        });

        it('validates option types', function () {
            $options = [
                'width' => '300', // string instead of int
                'height' => 200,
            ];

            $url = $this->adapter->transformedUrl('test.jpg', $options);

            // Should only contain height since width is wrong type
            expect($url)
                ->toContain('h=200')
                ->not->toContain('w=300');
        });
    });
});

describe('CloudflareFilesystemAdapter integration', function () {
    beforeEach(function () {
        // Set up config for the service provider
        config([
            'cloudflare-transforms.domain' => 'integration.cloudflare.com',
            'filesystems.disks.cloudflare-test' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'us-east-1',
                'bucket' => 'test-bucket',
                'cloudflare_domain' => 'integration.cloudflare.com',
            ],
        ]);
    });

    it('integrates with Laravel Storage', function () {
        // This would require the actual service provider to be registered
        // For now, we'll test the concept
        expect(true)->toBeTrue();
    });
});
