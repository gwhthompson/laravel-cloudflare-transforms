<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

use Gwhthompson\CloudflareTransforms\Contracts\CloudflareImageContract;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

/**
 * Fluent API builder for constructing Cloudflare Image Transformation URLs.
 *
 * Provides chainable methods for all Cloudflare image transformation parameters
 * (width, height, format, quality, fit, etc.) and generates the final transformed URL.
 *
 * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/
 */
class CloudflareImage implements CloudflareImageContract
{
    /** @var array<string, string> */
    private array $transforms = [];

    private function __construct(
        private readonly string $path,
        private readonly string $domain,
        private readonly string $disk,
        private readonly string $transformPath,
        private readonly bool $validateExists = true,
    ) {}

    public function __toString(): string
    {
        return $this->url();
    }

    /** Whether to preserve animation frames. Default true. */
    public function anim(bool $preserve = true): self
    {
        return $this->with('anim', $preserve);
    }

    /** Background color for transparent images (e.g., PNG) and images resized with fit=pad. */
    public function background(string $color): self
    {
        return $this->with('background', $color);
    }

    /** Blur radius between 1 (slight blur) and 250 (maximum). */
    public function blur(float $blur): self
    {
        return $blur >= 1 && $blur <= 250
            ? $this->with('blur', $blur)
            : throw new InvalidArgumentException('Blur must be 1-250');
    }

    /** Brightness multiplier. 1.0 = no change, 0.5 = half brightness, 2.0 = twice as bright. */
    public function brightness(float $brightness): self
    {
        return $brightness >= 0 && $brightness <= 2
            ? $this->with('brightness', $brightness)
            : throw new InvalidArgumentException('Brightness must be 0-2');
    }

    public function compression(string $compression = 'fast'): self
    {
        return $compression === 'fast'
            ? $this->with('compression', $compression)
            : throw new InvalidArgumentException('Compression must be "fast"');
    }

    public function contrast(float $contrast): self
    {
        return $contrast >= 0 && $contrast <= 2
            ? $this->with('contrast', $contrast)
            : throw new InvalidArgumentException('Contrast must be 0-2');
    }

    public function dpr(float $dpr): self
    {
        return $dpr >= 0.1 && $dpr <= 5
            ? $this->with('dpr', $dpr)
            : throw new InvalidArgumentException('DPR must be 0.1-5');
    }

    /** How to resize the image within the given dimensions. */
    public function fit(Fit $fit): self
    {
        return $this->with('fit', $fit->value);
    }

    /** Flip the image horizontally, vertically, or both. */
    public function flip(Flip $flip): self
    {
        return $this->with('flip', $flip->value);
    }

    /** Output format. Use Format::Auto for WebP/AVIF in supported browsers. */
    public function format(Format $format): self
    {
        return $this->with('f', $format->value);
    }

    public function gamma(float $gamma): self
    {
        return $gamma >= 0 && $gamma <= 2
            ? $this->with('gamma', $gamma)
            : throw new InvalidArgumentException('Gamma must be 0-2');
    }

    /** Focal point for cropping. Accepts Gravity enum or "XxY" coordinates (0.0-1.0). */
    public function gravity(Gravity|string $gravity): self
    {
        if ($gravity instanceof Gravity) {
            return $this->with('gravity', $gravity->value);
        }

        $value = $gravity;

        if (preg_match('/^(0|0\.\d+|1|1\.0*)x(0|0\.\d+|1|1\.0*)$/', $value)) {
            return $this->with('gravity', $value);
        }

        throw new InvalidArgumentException('Invalid gravity');
    }

    public function grayscale(): self
    {
        return $this->saturation(0);
    }

    /** Maximum height in pixels. Behavior depends on fit mode. */
    public function height(int $height): self
    {
        return $height >= 1 && $height <= 12000
            ? $this->with('h', $height)
            : throw new InvalidArgumentException('Height must be 1-12,000');
    }

    /**
     * Create a new CloudflareImage instance.
     *
     * @param  bool|null  $validateExists  Whether to check file exists before generating URL (default: from config)
     */
    public static function make(
        string $path,
        ?string $domain = null,
        ?string $disk = null,
        ?string $transformPath = null,
        ?bool $validateExists = null,
    ): self {
        // Type-safe config access with proper narrowing for PHPStan level 9
        $domainConfig = Config::get('cloudflare-transforms.domain', '');
        $domain ??= is_string($domainConfig) ? $domainConfig : '';

        $diskConfig = Config::get('cloudflare-transforms.disk') ?? Config::get('filesystems.default', 'public');
        $disk ??= is_string($diskConfig) ? $diskConfig : 'public';

        $pathConfig = Config::get('cloudflare-transforms.transform_path', 'cdn-cgi/image');
        $transformPath ??= is_string($pathConfig) ? $pathConfig : 'cdn-cgi/image';

        $validateConfig = Config::get('cloudflare-transforms.validate_file_exists', true);
        $validateExists ??= is_bool($validateConfig) ? $validateConfig : true;

        // If no domain is configured, fall back to parsing the current APP_URL
        if ($domain === '') {
            $appUrlConfig = Config::get('app.url', 'http://localhost');
            $appUrl = is_string($appUrlConfig) ? $appUrlConfig : 'http://localhost';
            $parsedHost = parse_url($appUrl, PHP_URL_HOST);
            $domain = is_string($parsedHost) ? $parsedHost : 'localhost';
        }

        return new self(
            path: $path,
            domain: $domain,
            disk: $disk,
            transformPath: $transformPath,
            validateExists: $validateExists,
        );
    }

    public function metadata(Metadata $metadata): self
    {
        return $this->with('metadata', $metadata->value);
    }

    public function onerror(string $action = 'redirect'): self
    {
        return $action === 'redirect'
            ? $this->with('onerror', $action)
            : throw new InvalidArgumentException('OnError must be "redirect"');
    }

    public function optimize(): self
    {
        return $this->format(Format::Auto)->quality(Quality::High);
    }

    /** Quality for JPEG, WebP, and AVIF formats (1-100 or Quality enum). */
    public function quality(Quality|int $quality): self
    {
        return $this->validateAndSetQuality($quality, 'q');
    }

    public function responsive(int $width, float $dpr = 1): self
    {
        return $this->width($width)->dpr($dpr)->format(Format::Auto);
    }

    public function rotate(int $degrees): self
    {
        return in_array($degrees, [90, 180, 270])
            ? $this->with('rotate', $degrees)
            : throw new InvalidArgumentException('Rotation must be 90, 180, or 270');
    }

    public function saturation(float $saturation): self
    {
        return $saturation >= 0 && $saturation <= 2
            ? $this->with('saturation', $saturation)
            : throw new InvalidArgumentException('Saturation must be 0-2');
    }

    public function sharpen(float $sharpen): self
    {
        return $sharpen >= 0 && $sharpen <= 10
            ? $this->with('sharpen', $sharpen)
            : throw new InvalidArgumentException('Sharpen must be 0-10');
    }

    public function thumbnail(int $size = 150): self
    {
        return $this->width($size)->height($size)->fit(Fit::Cover);
    }

    public function trim(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self
    {
        if ($top < 0 || $right < 0 || $bottom < 0 || $left < 0) {
            throw new InvalidArgumentException('Trim values must be non-negative');
        }

        return $this->with('trim', "{$top};{$right};{$bottom};{$left}");
    }

    public function trimBorder(?string $color = null, ?int $tolerance = null, ?int $keep = null): self
    {
        $instance = $this->with('trim', 'border');

        if ($color !== null) {
            $instance = $instance->with('trim.border.color', $color);
        }

        if ($tolerance !== null) {
            if ($tolerance < 0 || $tolerance > 255) {
                throw new InvalidArgumentException('Tolerance must be between 0-255');
            }

            $instance = $instance->with('trim.border.tolerance', $tolerance);
        }

        if ($keep !== null) {
            if ($keep < 0) {
                throw new InvalidArgumentException('Keep must be 0 or greater');
            }

            $instance = $instance->with('trim.border.keep', $keep);
        }

        return $instance;
    }

    /** Generate the final Cloudflare transformation URL. */
    public function url(): string
    {
        // Validate path - check both raw and URL-decoded for traversal attempts
        $decodedPath = urldecode($this->path);
        if ($this->path === '' || $this->path === '0' || str_contains($this->path, '..') || str_contains($decodedPath, '..')) {
            throw new InvalidArgumentException('Invalid path');
        }

        // Optional file existence check (can be disabled for performance)
        if ($this->validateExists && ! Storage::disk($this->disk)->exists($this->path)) {
            throw new InvalidArgumentException("File does not exist: {$this->path}");
        }

        $baseUrl = $this->buildBaseUrl();

        return $this->transforms === []
            ? $baseUrl
            : $this->buildTransformUrl();
    }

    /** Maximum width in pixels. Use auto=true for automatic responsive sizing. */
    public function width(int $width = 640, bool $auto = false): self
    {
        return match (true) {
            $auto => $this->with('w', 'auto'),
            default => $width >= 1 && $width <= 12000
                ? $this->with('w', $width)
                : throw new InvalidArgumentException("Width must be 1-12,000 or 'auto'")
        };
    }

    public function zoom(float $zoom): self
    {
        return match (true) {
            ! ($zoom >= 0 && $zoom <= 1) => throw new InvalidArgumentException('Zoom must be 0-1'),
            ($this->transforms['gravity'] ?? null) !== 'face' => throw new InvalidArgumentException('Zoom requires gravity=face'),
            default => $this->with('zoom', $zoom)
        };
    }

    /**
     * Add a border around the image.
     *
     * Border is added after resizing and accounts for DPR.
     * Use either $width for uniform border, or individual side parameters.
     *
     * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/#border
     */
    public function border(
        ?string $color = null,
        ?int $width = null,
        ?int $top = null,
        ?int $right = null,
        ?int $bottom = null,
        ?int $left = null
    ): self {
        $parts = [];

        if ($color !== null) {
            $parts[] = "color:{$color}";
        }

        if ($width !== null) {
            if ($width < 0) {
                throw new InvalidArgumentException('Border width must be non-negative');
            }

            $parts[] = "width:{$width}";
        }

        // Individual sides (if any specified, use these instead of width)
        $sides = ['top' => $top, 'right' => $right, 'bottom' => $bottom, 'left' => $left];
        foreach ($sides as $side => $value) {
            if ($value !== null) {
                if ($value < 0) {
                    throw new InvalidArgumentException("Border {$side} must be non-negative");
                }

                $parts[] = "{$side}:{$value}";
            }
        }

        if ($parts === []) {
            throw new InvalidArgumentException('Border requires at least one parameter');
        }

        return $this->with('border', implode('_', $parts));
    }

    /**
     * Automatically isolate the subject by replacing background with transparency.
     *
     * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/#segment
     */
    public function segment(string $mode = 'foreground'): self
    {
        return $mode === 'foreground'
            ? $this->with('segment', $mode)
            : throw new InvalidArgumentException('Segment must be "foreground"');
    }

    /**
     * Alternative quality for slow network connections.
     *
     * Triggered when RTT >150ms, save-data enabled, ECT is 2g/3g, or downlink <5Mbps.
     *
     * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/#slow-connection-quality
     */
    public function slowConnectionQuality(Quality|int $quality): self
    {
        return $this->validateAndSetQuality($quality, 'scq');
    }

    private function buildBaseUrl(): string
    {
        return "https://{$this->domain}/{$this->path}";
    }

    private function buildTransformUrl(): string
    {
        $options = array_map(
            fn ($key, $value): string => match ($key) {
                'background' => 'background='.urlencode($value),
                'anim' => 'anim='.($value !== '' && $value !== '0' ? 'true' : 'false'),
                default => "{$key}={$value}"
            },
            array_keys($this->transforms),
            $this->transforms
        );

        return "https://{$this->domain}/{$this->transformPath}/"
            .implode(',', $options)
            ."/{$this->path}";
    }

    private function validateAndSetQuality(Quality|int $quality, string $key): self
    {
        return match (true) {
            $quality instanceof Quality => $this->with($key, $quality->value),
            $quality >= 1 && $quality <= 100 => $this->with($key, $quality),
            default => throw new InvalidArgumentException('Quality must be 1-100 or Quality enum'),
        };
    }

    private function with(string $key, mixed $value): self
    {
        if (! is_scalar($value)) {
            throw new InvalidArgumentException("Value for {$key} must be scalar");
        }

        $value = strval($value);
        $this->transforms[$key] = $value;

        return $this;
    }
}
