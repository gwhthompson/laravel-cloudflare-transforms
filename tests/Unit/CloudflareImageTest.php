<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\CloudflareImage;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Gwhthompson\CloudflareTransforms\Exceptions\ConfigurationException;
use Gwhthompson\CloudflareTransforms\Exceptions\InvalidTransformParameterException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('test.jpg', 'fake content');
    Storage::disk('public')->put('test.gif', 'fake gif content');

    Config::set('cloudflare-transforms.domain', 'example.cloudflare.com');
    Config::set('cloudflare-transforms.disk', 'public');
    Config::set('cloudflare-transforms.transform_path', 'cdn-cgi/image');
});

describe('CloudflareImage creation', function () {
    it('can create instance with static make method')
        ->expect(fn () => CloudflareImage::make('test.jpg'))
        ->toBeInstanceOf(CloudflareImage::class);

    it('can be cast to string')
        ->expect(fn () => (string) CloudflareImage::make('test.jpg')->width(300))
        ->toBe('https://example.cloudflare.com/cdn-cgi/image/w=300/test.jpg');

    it('supports fluent interface chaining', function () {
        $image = CloudflareImage::make('test.jpg')
            ->width(300)
            ->height(200)
            ->quality(85)
            ->fit(Fit::Cover);

        expect($image)->toBeInstanceOf(CloudflareImage::class);
        expect($image->url())->toContain('w=300,h=200,q=85,fit=cover');
    });
});

describe('URL generation', function () {
    it('generates basic URL without transformations')
        ->expect(fn () => CloudflareImage::make('test.jpg')->url())
        ->toBe('https://example.cloudflare.com/test.jpg');

    it('generates URL with single transformation')
        ->expect(fn () => CloudflareImage::make('test.jpg')->width(300)->url())
        ->toBe('https://example.cloudflare.com/cdn-cgi/image/w=300/test.jpg');

    it('generates URL with multiple transformations')
        ->expect(fn () => CloudflareImage::make('test.jpg')
            ->width(300)
            ->height(200)
            ->format(Format::Webp)
            ->url())
        ->toBe('https://example.cloudflare.com/cdn-cgi/image/w=300,h=200,f=webp/test.jpg');

    it('generates URLs with transformation combinations', function (array $transforms, string $expected) {
        $image = CloudflareImage::make('test.jpg');

        foreach ($transforms as $method => $value) {
            $image->$method($value);
        }

        expect($image->url())->toContain($expected);
    })->with('transformation_combinations');
});

describe('parameter validation', function () {
    it('validates parameter ranges', function (string $method, mixed $value, string $expectedMessage) {
        expect(fn () => CloudflareImage::make('test.jpg')->$method($value))
            ->toThrow(InvalidTransformParameterException::class, $expectedMessage);
    })->with('validation_ranges');

    it('validates trim border parameters', function (int $value, string $expectedMessage) {
        if (str_contains(strtolower($expectedMessage), 'tolerance')) {
            expect(fn () => CloudflareImage::make('test.jpg')->trimBorder(tolerance: $value))
                ->toThrow(InvalidTransformParameterException::class, $expectedMessage);
        } else {
            expect(fn () => CloudflareImage::make('test.jpg')->trimBorder(keep: $value))
                ->toThrow(InvalidTransformParameterException::class, $expectedMessage);
        }
    })->with('trim_border_validation');

    it('validates gravity coordinates', function (string $gravity, bool $shouldPass) {
        if ($shouldPass) {
            $url = CloudflareImage::make('test.jpg')->gravity($gravity)->url();
            expect($url)->toContain("gravity={$gravity}");
        } else {
            expect(fn () => CloudflareImage::make('test.jpg')->gravity($gravity))
                ->toThrow(InvalidTransformParameterException::class, 'Invalid gravity');
        }
    })->with('gravity_coordinates');
});

describe('special cases', function () {
    it('handles gravity with enum')
        ->expect(fn () => CloudflareImage::make('test.jpg')->gravity(Gravity::Face)->url())
        ->toContain('gravity=face');

    it('handles special encoding cases', function (mixed $value, string $expected) {
        if (is_bool($value)) {
            $url = CloudflareImage::make('test.gif')->anim($value)->url();
        } else {
            $url = CloudflareImage::make('test.jpg')->background($value)->url();
        }

        expect($url)->toContain($expected);
    })->with('special_encoding_cases');

    it('handles trim border with nested parameters', function () {
        $url = CloudflareImage::make('test.jpg')
            ->trimBorder('#ffffff', 10, 5)
            ->url();

        expect($url)
            ->toContain('trim=border')
            ->toContain('trim.border.color=#ffffff')
            ->toContain('trim.border.tolerance=10')
            ->toContain('trim.border.keep=5');
    });
});

describe('path validation', function () {
    it('validates invalid paths', function (string $path, string $expectedMessage, string $exceptionClass) {
        expect(fn () => CloudflareImage::make($path)->url())
            ->toThrow($exceptionClass, $expectedMessage);
    })->with('invalid_paths');
});

describe('configuration fallbacks', function () {
    it('throws ConfigurationException when no domain configured', function () {
        Config::set('cloudflare-transforms.domain', null);
        Config::set('app.url', null);

        expect(fn () => CloudflareImage::make('test.jpg'))
            ->toThrow(ConfigurationException::class, 'No Cloudflare domain configured');
    });

    it('can be created with custom parameters', function () {
        Storage::fake('local');
        Storage::disk('local')->put('test.jpg', 'fake content');

        $url = CloudflareImage::make(
            'test.jpg',
            'custom.domain.com',
            'local',
            'image'
        )->width(300)->url();

        expect($url)->toBe('https://custom.domain.com/image/w=300/test.jpg');
    });
});

describe('enum integration', function () {
    it('works with all enum types', function () {
        $url = CloudflareImage::make('test.jpg')
            ->fit(Fit::Cover)
            ->format(Format::Webp)
            ->quality(Quality::High)
            ->gravity(Gravity::Face)
            ->url();

        expect($url)->toContain('fit=cover,f=webp,q=high,gravity=face');
    });

    it('can mix enums with scalar values', function () {
        $url = CloudflareImage::make('test.jpg')
            ->width(300)
            ->fit(Fit::Crop)
            ->quality(85)
            ->url();

        expect($url)->toContain('w=300,fit=crop,q=85');
    });
});

describe('srcset generation', function () {
    it('generates srcset with width descriptors', function () {
        $srcset = CloudflareImage::make('test.jpg')
            ->format(Format::Auto)
            ->srcset([320, 640, 960]);

        expect($srcset)
            ->toContain('w=320')
            ->toContain('f=auto')
            ->toContain('test.jpg 320w')
            ->toContain('test.jpg 640w')
            ->toContain('test.jpg 960w');
    });

    it('generates srcset with density descriptors', function () {
        $srcset = CloudflareImage::make('test.jpg')
            ->format(Format::Auto)
            ->srcsetDensity(480);

        expect($srcset)
            ->toContain('w=480')
            ->toContain('w=960')
            ->toContain('f=auto')
            ->toContain('test.jpg 1x')
            ->toContain('test.jpg 2x');
    });

    it('preserves other transforms in srcset', function () {
        $srcset = CloudflareImage::make('test.jpg')
            ->fit(Fit::Cover)
            ->quality(Quality::High)
            ->srcset([400, 800]);

        expect($srcset)
            ->toContain('fit=cover')
            ->toContain('q=high')
            ->toContain('w=400')
            ->toContain('w=800')
            ->toContain('test.jpg 400w')
            ->toContain('test.jpg 800w');
    });

    it('handles srcset with single width', function () {
        $srcset = CloudflareImage::make('test.jpg')->srcset([600]);

        expect($srcset)->toBe('https://example.cloudflare.com/cdn-cgi/image/w=600/test.jpg 600w');
    });

    it('handles srcsetDensity with transforms', function () {
        $srcset = CloudflareImage::make('test.jpg')
            ->fit(Fit::Cover)
            ->srcsetDensity(300);

        expect($srcset)
            ->toContain('fit=cover,w=300/test.jpg 1x')
            ->toContain('fit=cover,w=600/test.jpg 2x');
    });

    it('overrides existing width in srcset', function () {
        $srcset = CloudflareImage::make('test.jpg')
            ->width(100)
            ->srcset([200, 400]);

        // Width should be overridden by srcset widths
        expect($srcset)
            ->toContain('w=200/test.jpg 200w')
            ->toContain('w=400/test.jpg 400w')
            ->not->toContain('w=100');
    });

    it('throws on empty srcset array', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->srcset([]))
            ->toThrow(InvalidTransformParameterException::class, 'Srcset widths array cannot be empty');
    });

    it('validates width values in srcset', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->srcset([0, 300]))
            ->toThrow(InvalidTransformParameterException::class, 'Width must be between 1 and 12000');
    });

    it('validates srcsetDensity base width cannot exceed half of max', function () {
        // Max is 12,000, so 2x of 7,000 = 14,000 exceeds max
        expect(fn () => CloudflareImage::make('test.jpg')->srcsetDensity(7000))
            ->toThrow(InvalidTransformParameterException::class, 'Base width must be between 1 and 6000');
    });

    it('accepts srcsetDensity base width at max valid value', function () {
        // Max is 12,000, so 2x of 6,000 = 12,000 is exactly at max
        $srcset = CloudflareImage::make('test.jpg')->srcsetDensity(6000);

        expect($srcset)
            ->toContain('w=6000')
            ->toContain('w=12000')
            ->toContain('test.jpg 1x')
            ->toContain('test.jpg 2x');
    });
});

describe('untested transformation methods', function () {
    it('applies compression transform')
        ->expect(fn () => CloudflareImage::make('test.jpg')->compression('fast')->url())
        ->toContain('compression=fast');

    it('applies dpr transform')
        ->expect(fn () => CloudflareImage::make('test.jpg')->dpr(2.0)->url())
        ->toContain('dpr=2');

    it('applies grayscale as saturation zero')
        ->expect(fn () => CloudflareImage::make('test.jpg')->grayscale()->url())
        ->toContain('saturation=0');

    it('applies onerror transform')
        ->expect(fn () => CloudflareImage::make('test.jpg')->onerror('redirect')->url())
        ->toContain('onerror=redirect');

    it('applies optimize preset with auto format and high quality')
        ->expect(fn () => CloudflareImage::make('test.jpg')->optimize()->url())
        ->toContain('f=auto')
        ->toContain('q=high');

    it('applies responsive preset with width dpr and format')
        ->expect(fn () => CloudflareImage::make('test.jpg')->responsive(800, 2)->url())
        ->toContain('w=800')
        ->toContain('dpr=2')
        ->toContain('f=auto');

    it('applies width auto for responsive sizing')
        ->expect(fn () => CloudflareImage::make('test.jpg')->width(auto: true)->url())
        ->toContain('w=auto');

    it('applies segment foreground transform')
        ->expect(fn () => CloudflareImage::make('test.jpg')->segment('foreground')->url())
        ->toContain('segment=foreground');

    it('applies slowConnectionQuality with integer')
        ->expect(fn () => CloudflareImage::make('test.jpg')->slowConnectionQuality(50)->url())
        ->toContain('scq=50');

    it('applies slowConnectionQuality with Quality enum')
        ->expect(fn () => CloudflareImage::make('test.jpg')->slowConnectionQuality(Quality::Low)->url())
        ->toContain('scq=low');

    it('applies zoom with gravity face')
        ->expect(fn () => CloudflareImage::make('test.jpg')->gravity(Gravity::Face)->zoom(0.5)->url())
        ->toContain('gravity=face')
        ->toContain('zoom=0.5');

    it('applies thumbnail preset with size fit cover')
        ->expect(fn () => CloudflareImage::make('test.jpg')->thumbnail(200)->url())
        ->toContain('w=200')
        ->toContain('h=200')
        ->toContain('fit=cover');
});

describe('validation edge cases', function () {
    it('throws for negative trim values', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->trim(-1, 0, 0, 0))
            ->toThrow(InvalidTransformParameterException::class, 'non-negative');
    });

    it('throws for zoom without gravity face', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->zoom(0.5))
            ->toThrow(InvalidTransformParameterException::class, 'gravity=face');
    });

    it('throws for zoom when gravity is not face at zoom call time', function () {
        expect(fn () => CloudflareImage::make('test.jpg')
            ->gravity(Gravity::Auto)->zoom(0.5)->url())
            ->toThrow(InvalidTransformParameterException::class, 'gravity=face');
    });

    it('throws for zoom when gravity is changed after zoom in buildTransformUrl', function () {
        // This tests the deferred validation in buildTransformUrl() - line 426
        // The zoom() method passes because gravity=face, but then gravity is changed
        $image = CloudflareImage::make('test.jpg')
            ->gravity(Gravity::Face)
            ->zoom(0.5);

        // Change gravity to something other than face
        $image->gravity(Gravity::Auto);

        // Now url() should throw because buildTransformUrl checks zoom requires gravity=face
        expect(fn () => $image->url())
            ->toThrow(InvalidTransformParameterException::class, 'gravity=face');
    });

    it('throws for quality out of range', function (int $quality) {
        expect(fn () => CloudflareImage::make('test.jpg')->quality($quality))
            ->toThrow(InvalidTransformParameterException::class, 'between 1 and 100');
    })->with([0, 101]);

    it('throws for invalid onerror value', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->onerror('invalid'))
            ->toThrow(InvalidTransformParameterException::class, 'must be "redirect"');
    });

    it('throws for invalid compression value', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->compression('slow'))
            ->toThrow(InvalidTransformParameterException::class, 'must be "fast"');
    });

    it('throws for invalid segment value', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->segment('background'))
            ->toThrow(InvalidTransformParameterException::class, 'must be "foreground"');
    });

    it('throws for invalid rotation value', function () {
        expect(fn () => CloudflareImage::make('test.jpg')->rotate(45))
            ->toThrow(InvalidTransformParameterException::class);
    });

    it('applies trim with valid pixel values', function () {
        $url = CloudflareImage::make('test.jpg')->trim(10, 20, 30, 40)->url();
        expect($url)->toContain('trim=10;20;30;40');
    });

    it('applies valid rotation values', function (int $degrees) {
        $url = CloudflareImage::make('test.jpg')->rotate($degrees)->url();
        expect($url)->toContain("rotate={$degrees}");
    })->with([90, 180, 270]);
});
