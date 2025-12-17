<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Exceptions\ConfigurationException;
use Gwhthompson\CloudflareTransforms\Exceptions\FileNotFoundException;

describe('ConfigurationException', function () {
    it('creates missingDomain exception with helpful message', function () {
        $exception = ConfigurationException::missingDomain();

        expect($exception)
            ->toBeInstanceOf(ConfigurationException::class)
            ->getMessage()->toContain('No Cloudflare domain configured');
    });

    it('creates invalidDomain exception with domain in message', function () {
        $exception = ConfigurationException::invalidDomain('bad-domain');

        expect($exception)
            ->toBeInstanceOf(ConfigurationException::class)
            ->getMessage()->toContain('bad-domain');
    });
});

describe('FileNotFoundException', function () {
    it('creates exception without disk parameter', function () {
        $exception = FileNotFoundException::forPath('missing.jpg');

        expect($exception->getMessage())
            ->toContain('missing.jpg')
            ->not->toContain('disk');
    });

    it('creates exception with disk parameter', function () {
        $exception = FileNotFoundException::forPath('missing.jpg', 's3');

        expect($exception->getMessage())
            ->toContain('missing.jpg')
            ->toContain('s3');
    });

    it('includes path in message for both formats', function () {
        $withoutDisk = FileNotFoundException::forPath('images/photo.jpg');
        $withDisk = FileNotFoundException::forPath('images/photo.jpg', 'public');

        expect($withoutDisk->getMessage())->toContain('images/photo.jpg');
        expect($withDisk->getMessage())->toContain('images/photo.jpg');
    });
});
