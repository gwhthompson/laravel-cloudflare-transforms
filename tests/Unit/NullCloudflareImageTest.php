<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\NullCloudflareImage;

describe('NullCloudflareImage', function () {
    beforeEach(function () {
        $this->originalUrl = 'https://example.com/test.jpg';
        $this->nullImage = new NullCloudflareImage($this->originalUrl);
    });

    it('returns original URL when cast to string', function () {
        expect((string)$this->nullImage)->toBe($this->originalUrl);
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

    it('handles magic method calls for unknown methods', function () {
        $result = $this->nullImage
            ->unknownMethod('value')
            ->anotherMethod(100)
            ->chainedMethod('test', 200, true);

        expect($result)
            ->toBeInstanceOf(NullCloudflareImage::class)
            ->url()->toBe($this->originalUrl);
    });

    it('preserves original URL through extensive chaining', function () {
        $result = $this->nullImage
            ->width(300)
            ->height(200)
            ->format('webp')
            ->quality(85)
            ->blur(10)
            ->brightness(0.5)
            ->contrast(1.2)
            ->gamma(1.5)
            ->nonExistentMethod('test')
            ->anotherChain(100, true, 'string');

        expect($result->url())->toBe($this->originalUrl);
    });
});