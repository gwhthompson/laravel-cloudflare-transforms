<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.jpg', 'fake content');
});

describe('Enum values', function () {
    it('has correct Fit enum values', function ($enum, $expected) {
        expect($enum->value)->toBe($expected);
    })->with('fit_enum_values');

    it('has correct Format enum values', function ($enum, $expected) {
        expect($enum->value)->toBe($expected);
    })->with('format_enum_values');

    it('has correct Quality enum values', function ($enum, $expected) {
        expect($enum->value)->toBe($expected);
    })->with('quality_enum_values');

    it('has correct Gravity enum values', function ($enum, $expected) {
        expect($enum->value)->toBe($expected);
    })->with('gravity_enum_values');

    it('has correct Flip enum values', function ($enum, $expected) {
        expect($enum->value)->toBe($expected);
    })->with('flip_enum_values');

    it('has correct Metadata enum values', function ($enum, $expected) {
        expect($enum->value)->toBe($expected);
    })->with('metadata_enum_values');
});

describe('Enum integration with CloudflareImage', function () {
    it('applies enum transformations correctly', function (string $method, $enum, string $expected) {
        $url = CloudflareImage::make('test.jpg')->$method($enum)->url();

        expect($url)->toContain($expected);
    })->with('enum_transformations');

    it('can combine all enum types')
        ->expect(fn () => CloudflareImage::make('test.jpg')
            ->fit(Fit::Cover)
            ->format(Format::Webp)
            ->quality(Quality::High)
            ->gravity(Gravity::Face)
            ->flip(Flip::Horizontal)
            ->metadata(Metadata::None)
            ->url())
        ->toContain('fit=cover')
        ->toContain('f=webp')
        ->toContain('q=high')
        ->toContain('gravity=face')
        ->toContain('flip=h')
        ->toContain('metadata=none');

    it('can mix enums with scalar values')
        ->expect(fn () => CloudflareImage::make('test.jpg')
            ->width(300)
            ->fit(Fit::Crop)
            ->quality(85)
            ->url())
        ->toContain('w=300')
        ->toContain('fit=crop')
        ->toContain('q=85');
});

describe('Enum completeness', function () {
    it('covers all Cloudflare transformation options', function () {
        // Verify we have all the main Cloudflare Image Transformation options
        expect(Fit::cases())->toHaveCount(6);
        expect(Format::cases())->toHaveCount(6);
        expect(Quality::cases())->toHaveCount(4);
        expect(Gravity::cases())->toHaveCount(6);
        expect(Flip::cases())->toHaveCount(3);
        expect(Metadata::cases())->toHaveCount(3);

        // Test that all enum cases have values
        foreach (Fit::cases() as $case) {
            expect($case)->toHaveProperty('value');
        }
    });
});
