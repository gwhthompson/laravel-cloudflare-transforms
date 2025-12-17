<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Gwhthompson\CloudflareTransforms\View\Components\Image;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.jpg', 'fake content');
    Storage::disk('public')->put('hero.jpg', 'fake content');

    Config::set('cloudflare-transforms.domain', 'cdn.example.com');
    Config::set('cloudflare-transforms.disk', 'public');
    Config::set('cloudflare-transforms.transform_path', 'cdn-cgi/image');
    Config::set('cloudflare-transforms.validate_file_exists', false);

    // Configure a disk with URL for Cloudflare transforms
    Config::set('filesystems.disks.media', [
        'driver' => 'local',
        'root' => Storage::disk('public')->path(''),
        'url' => 'https://cdn.example.com',
    ]);
});

describe('Image Blade component', function () {
    describe('component instantiation', function () {
        it('can be instantiated with minimal parameters', function () {
            $component = new Image(path: 'test.jpg');

            expect($component->path)->toBe('test.jpg');
            expect($component->disk)->toBe('public');
        });

        it('accepts custom disk', function () {
            $component = new Image(path: 'test.jpg', disk: 'media');

            expect($component->disk)->toBe('media');
        });

        it('accepts all transform parameters', function () {
            $component = new Image(
                path: 'test.jpg',
                width: 800,
                height: 600,
                format: Format::Auto,
                fit: Fit::Cover,
            );

            expect($component->width)->toBe(800);
            expect($component->height)->toBe(600);
            expect($component->format)->toBe(Format::Auto);
            expect($component->fit)->toBe(Fit::Cover);
        });

        it('accepts srcset parameters', function () {
            $component = new Image(
                path: 'test.jpg',
                srcset: [400, 800, 1200],
                sizes: '(max-width: 600px) 100vw, 50vw',
            );

            expect($component->srcset)->toBe([400, 800, 1200]);
            expect($component->sizes)->toBe('(max-width: 600px) 100vw, 50vw');
        });

        it('accepts srcsetDensity parameter', function () {
            $component = new Image(path: 'test.jpg', srcsetDensity: 480);

            expect($component->srcsetDensity)->toBe(480);
        });
    });

    describe('srcAttribute method', function () {
        it('returns URL with largest srcset width', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                srcset: [400, 800, 1200],
            );

            $src = $component->srcAttribute();

            expect($src)->toContain('w=1200');
        });

        it('returns URL with 2x width for density srcset', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                srcsetDensity: 480,
            );

            $src = $component->srcAttribute();

            expect($src)->toContain('w=960');
        });

        it('uses specified width when no srcset', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                width: 600,
            );

            $src = $component->srcAttribute();

            expect($src)->toContain('w=600');
        });
    });

    describe('srcsetAttribute method', function () {
        it('returns srcset string for width breakpoints', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                srcset: [400, 800],
            );

            $srcset = $component->srcsetAttribute();

            expect($srcset)
                ->toContain('400w')
                ->toContain('800w');
        });

        it('returns srcset string for density descriptors', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                srcsetDensity: 300,
            );

            $srcset = $component->srcsetAttribute();

            expect($srcset)
                ->toContain('1x')
                ->toContain('2x');
        });

        it('returns null when no srcset configured', function () {
            $component = new Image(path: 'test.jpg', disk: 'media');

            expect($component->srcsetAttribute())->toBeNull();
        });

        it('preserves transforms in srcset', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                format: Format::Auto,
                fit: Fit::Cover,
                srcset: [400, 800],
            );

            $srcset = $component->srcsetAttribute();

            expect($srcset)
                ->toContain('f=auto')
                ->toContain('fit=cover');
        });
    });

    describe('component rendering', function () {
        it('renders img tag with src attribute', function () {
            $view = Blade::render(
                '<x-cloudflare::image path="test.jpg" disk="media" :width="400" />',
            );

            expect($view)
                ->toContain('<img')
                ->toContain('src=');
        });

        it('renders srcset attribute when configured', function () {
            $view = Blade::render(
                '<x-cloudflare::image path="test.jpg" disk="media" :srcset="[400, 800]" />',
            );

            expect($view)
                ->toContain('srcset=')
                ->toContain('400w')
                ->toContain('800w');
        });

        it('renders sizes attribute when provided', function () {
            $view = Blade::render(
                '<x-cloudflare::image path="test.jpg" disk="media" :srcset="[400, 800]" sizes="100vw" />',
            );

            expect($view)->toContain('sizes="100vw"');
        });

        it('passes through additional attributes', function () {
            $view = Blade::render(
                '<x-cloudflare::image path="test.jpg" disk="media" alt="Test image" class="w-full" />',
            );

            expect($view)
                ->toContain('alt="Test image"')
                ->toContain('class="w-full"');
        });

        it('renders with format enum', function () {
            $view = Blade::render(
                '<x-cloudflare::image path="test.jpg" disk="media" :format="\Gwhthompson\CloudflareTransforms\Enums\Format::Auto" />',
            );

            expect($view)->toContain('f=auto');
        });
    });

    describe('component property transforms', function () {
        it('applies height parameter to image builder', function () {
            $component = new Image(path: 'test.jpg', disk: 'media', height: 200);
            $url = $component->srcAttribute();
            expect($url)->toContain('h=200');
        });

        it('applies gravity enum parameter to image builder', function () {
            $component = new Image(path: 'test.jpg', disk: 'media', gravity: Gravity::Face);
            $url = $component->srcAttribute();
            expect($url)->toContain('gravity=face');
        });

        it('applies gravity string parameter to image builder', function () {
            $component = new Image(path: 'test.jpg', disk: 'media', gravity: '0.5x0.5');
            $url = $component->srcAttribute();
            expect($url)->toContain('gravity=0.5x0.5');
        });

        it('applies quality integer parameter to image builder', function () {
            $component = new Image(path: 'test.jpg', disk: 'media', quality: 85);
            $url = $component->srcAttribute();
            expect($url)->toContain('q=85');
        });

        it('applies quality enum parameter to image builder', function () {
            $component = new Image(path: 'test.jpg', disk: 'media', quality: Quality::High);
            $url = $component->srcAttribute();
            expect($url)->toContain('q=high');
        });

        it('combines all transform parameters', function () {
            $component = new Image(
                path: 'test.jpg',
                disk: 'media',
                width: 800,
                height: 600,
                format: Format::Webp,
                fit: Fit::Cover,
                gravity: Gravity::Auto,
                quality: 90,
            );
            $url = $component->srcAttribute();

            expect($url)
                ->toContain('w=800')
                ->toContain('h=600')
                ->toContain('f=webp')
                ->toContain('fit=cover')
                ->toContain('gravity=auto')
                ->toContain('q=90');
        });
    });
});
