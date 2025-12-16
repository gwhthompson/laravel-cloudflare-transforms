<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Contracts\CloudflareImageContract;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Gwhthompson\CloudflareTransforms\NullCloudflareImage;

describe('NullCloudflareImage', function () {
    beforeEach(function () {
        $this->originalUrl = 'https://example.com/test.jpg';
        $this->nullImage = new NullCloudflareImage($this->originalUrl);
    });

    it('implements CloudflareImageContract', function () {
        expect($this->nullImage)->toBeInstanceOf(CloudflareImageContract::class);
    });

    it('returns original URL when cast to string', function () {
        expect((string) $this->nullImage)->toBe($this->originalUrl);
    });

    it('returns original URL from url method', function () {
        expect($this->nullImage->url())->toBe($this->originalUrl);
    });

    it('maintains fluent interface for transformation methods', function (array $methods) {
        $result = $this->nullImage;

        foreach ($methods as $method => $args) {
            $result = $result->$method(...$args);
        }

        expect($result)->toBeInstanceOf(NullCloudflareImage::class)
            ->and($result)->toBe($this->nullImage)
            ->and($result->url())->toBe($this->originalUrl);
    })->with('chainable_methods');

    it('ignores all transformation calls', function (string $method, ...$args) {
        $result = $this->nullImage->$method(...$args);

        expect($result)
            ->toBeInstanceOf(NullCloudflareImage::class)
            ->url()->toBe($this->originalUrl);
    })->with('transformation_methods');

    it('preserves original URL through extensive chaining', function () {
        $result = $this->nullImage
            ->width(300)
            ->height(200)
            ->format(Format::Webp)
            ->quality(Quality::High)
            ->blur(10)
            ->brightness(0.5)
            ->contrast(1.2)
            ->gamma(1.5)
            ->segment('foreground')
            ->slowConnectionQuality(75);

        expect($result->url())->toBe($this->originalUrl);
    });

    it('supports all new Cloudflare parameters', function () {
        $result = $this->nullImage
            ->segment('foreground')
            ->slowConnectionQuality(Quality::Low);

        expect($result)
            ->toBeInstanceOf(NullCloudflareImage::class)
            ->url()->toBe($this->originalUrl);
    });

    it('returns proper srcset format with width descriptors', function () {
        $srcset = $this->nullImage->srcset([320, 640, 960]);

        expect($srcset)->toBe(
            'https://example.com/test.jpg 320w, https://example.com/test.jpg 640w, https://example.com/test.jpg 960w'
        );
    });

    it('returns proper srcset format with density descriptors', function () {
        $srcset = $this->nullImage->srcsetDensity(480);

        expect($srcset)->toBe(
            'https://example.com/test.jpg 1x, https://example.com/test.jpg 2x'
        );
    });

    it('ignores transforms before srcset but returns proper format', function () {
        $srcset = $this->nullImage
            ->width(300)
            ->format(Format::Auto)
            ->srcset([400, 800]);

        expect($srcset)->toBe(
            'https://example.com/test.jpg 400w, https://example.com/test.jpg 800w'
        );
    });

    it('returns original URL for empty srcset array', function () {
        $srcset = $this->nullImage->srcset([]);

        expect($srcset)->toBe($this->originalUrl);
    });
});
