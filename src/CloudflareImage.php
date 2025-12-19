<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms;

use Gwhthompson\CloudflareTransforms\Concerns\ValidatesTransformParameters;
use Gwhthompson\CloudflareTransforms\Contracts\CloudflareImageContract;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Gwhthompson\CloudflareTransforms\Exceptions\ConfigurationException;
use Gwhthompson\CloudflareTransforms\Exceptions\FileNotFoundException;
use Gwhthompson\CloudflareTransforms\Exceptions\InvalidTransformParameterException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

/**
 * Fluent API builder for constructing Cloudflare Image Transformation URLs.
 *
 * Provides chainable methods for all Cloudflare image transformation parameters
 * (width, height, format, quality, fit, etc.) and generates the final transformed URL.
 *
 * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/
 */
final class CloudflareImage implements CloudflareImageContract
{
    use ValidatesTransformParameters;

    // Dimension constraints (pixels)
    private const int DIMENSION_MIN = 1;

    private const int DIMENSION_MAX = 12_000;

    // Blur radius constraints
    private const float BLUR_MIN = 1.0;

    private const float BLUR_MAX = 250.0;

    // Image adjustment multipliers (brightness, contrast, gamma, saturation)
    private const float ADJUSTMENT_MIN = 0.0;

    private const float ADJUSTMENT_MAX = 2.0;

    // Quality constraints (percentage)
    private const int QUALITY_MIN = 1;

    private const int QUALITY_MAX = 100;

    // Other constraints
    private const float SHARPEN_MIN = 0.0;

    private const float SHARPEN_MAX = 10.0;

    private const float DPR_MIN = 0.1;

    private const float DPR_MAX = 5.0;

    private const float ZOOM_MIN = 0.0;

    private const float ZOOM_MAX = 1.0;

    private const int TOLERANCE_MIN = 0;

    private const int TOLERANCE_MAX = 255;

    /** @var array<int, int> */
    private const array VALID_ROTATIONS = [90, 180, 270];

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
        return $this->setValidatedFloat('blur', $blur, self::BLUR_MIN, self::BLUR_MAX, 'Blur');
    }

    /** Brightness multiplier. 1.0 = no change, 0.5 = half brightness, 2.0 = twice as bright. */
    public function brightness(float $brightness): self
    {
        return $this->setValidatedFloat('brightness', $brightness, self::ADJUSTMENT_MIN, self::ADJUSTMENT_MAX, 'Brightness');
    }

    public function compression(string $compression = 'fast'): self
    {
        return $this->setValidatedEquals('compression', $compression, 'fast', 'Compression');
    }

    public function contrast(float $contrast): self
    {
        return $this->setValidatedFloat('contrast', $contrast, self::ADJUSTMENT_MIN, self::ADJUSTMENT_MAX, 'Contrast');
    }

    public function dpr(float $dpr): self
    {
        return $this->setValidatedFloat('dpr', $dpr, self::DPR_MIN, self::DPR_MAX, 'DPR');
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
        return $this->setValidatedFloat('gamma', $gamma, self::ADJUSTMENT_MIN, self::ADJUSTMENT_MAX, 'Gamma');
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

        throw InvalidTransformParameterException::invalidPath('Invalid gravity coordinate format. Expected "XxY" where X and Y are between 0.0 and 1.0');
    }

    public function grayscale(): self
    {
        return $this->saturation(0);
    }

    /** Maximum height in pixels. Behavior depends on fit mode. */
    public function height(int $height): self
    {
        return $this->setValidatedInt('h', $height, self::DIMENSION_MIN, self::DIMENSION_MAX, 'Height');
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

        // Throw an exception if no domain is configured - fail fast with helpful message
        if ($domain === '') {
            throw ConfigurationException::missingDomain();
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
        return $this->setValidatedEquals('onerror', $action, 'redirect', 'OnError');
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
        return $this->setValidatedInSet('rotate', $degrees, self::VALID_ROTATIONS, 'Rotation');
    }

    public function saturation(float $saturation): self
    {
        return $this->setValidatedFloat('saturation', $saturation, self::ADJUSTMENT_MIN, self::ADJUSTMENT_MAX, 'Saturation');
    }

    public function sharpen(float $sharpen): self
    {
        return $this->setValidatedFloat('sharpen', $sharpen, self::SHARPEN_MIN, self::SHARPEN_MAX, 'Sharpen');
    }

    public function thumbnail(int $size = 150): self
    {
        return $this->width($size)->height($size)->fit(Fit::Cover);
    }

    public function trim(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self
    {
        if ($top < 0 || $right < 0 || $bottom < 0 || $left < 0) {
            throw new InvalidTransformParameterException('Trim values must be non-negative');
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
            if ($tolerance < self::TOLERANCE_MIN || $tolerance > self::TOLERANCE_MAX) {
                throw InvalidTransformParameterException::outOfRange('Tolerance', self::TOLERANCE_MIN, self::TOLERANCE_MAX);
            }

            $instance = $instance->with('trim.border.tolerance', $tolerance);
        }

        if ($keep !== null) {
            if ($keep < 0) {
                throw new InvalidTransformParameterException('Keep must be 0 or greater');
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
            throw InvalidTransformParameterException::invalidPath('Invalid path: path cannot be empty or contain directory traversal');
        }

        // Optional file existence check (can be disabled for performance)
        if ($this->validateExists && ! Storage::disk($this->disk)->exists($this->path)) {
            throw FileNotFoundException::forPath($this->path, $this->disk);
        }

        $baseUrl = $this->buildBaseUrl();

        return $this->transforms === []
            ? $baseUrl
            : $this->buildTransformUrl();
    }

    /** Maximum width in pixels. Use auto=true for automatic responsive sizing. */
    public function width(int $width = 640, bool $auto = false): self
    {
        return $auto
            ? $this->with('w', 'auto')
            : $this->setValidatedInt('w', $width, self::DIMENSION_MIN, self::DIMENSION_MAX, 'Width');
    }

    public function zoom(float $zoom): self
    {
        if (($this->transforms['gravity'] ?? null) !== 'face') {
            throw InvalidTransformParameterException::missingPrerequisite('Zoom', 'gravity=face');
        }

        return $this->setValidatedFloat('zoom', $zoom, self::ZOOM_MIN, self::ZOOM_MAX, 'Zoom');
    }

    /**
     * Automatically isolate the subject by replacing background with transparency.
     *
     * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/#segment
     */
    public function segment(string $mode = 'foreground'): self
    {
        return $this->setValidatedEquals('segment', $mode, 'foreground', 'Segment');
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

    /**
     * Generate srcset string with width descriptors for responsive layouts.
     *
     * Use with the HTML `sizes` attribute to let browsers select the optimal image.
     * Cloudflare recommends this approach for fluid/responsive layouts.
     *
     * @param  array<int>  $widths  Width breakpoints (e.g., [320, 640, 960, 1280])
     * @return string Srcset value: "url 320w, url 640w, ..."
     *
     * @see https://developers.cloudflare.com/images/transform-images/make-responsive-images/
     */
    public function srcset(array $widths): string
    {
        if ($widths === []) {
            throw new InvalidTransformParameterException('Srcset widths array cannot be empty');
        }

        return collect($widths)
            ->map(fn (int $w): string => $this->cloneWithWidth($w)->url()." {$w}w")
            ->implode(', ');
    }

    /**
     * Generate srcset string with density descriptors for high-DPI displays.
     *
     * Use for fixed-size images that need 1x and 2x versions for retina displays.
     * Cloudflare recommends not scaling images up, so source should be high-resolution.
     *
     * @param  int  $baseWidth  The 1x width (will generate 1x and 2x versions)
     * @return string Srcset value: "url 1x, url 2x"
     *
     * @see https://developers.cloudflare.com/images/transform-images/make-responsive-images/
     */
    public function srcsetDensity(int $baseWidth): string
    {
        $maxBaseWidth = (int) floor(self::DIMENSION_MAX / 2);

        if ($baseWidth < self::DIMENSION_MIN || $baseWidth > $maxBaseWidth) {
            throw InvalidTransformParameterException::outOfRange('Base width', self::DIMENSION_MIN, $maxBaseWidth);
        }

        return implode(', ', [
            $this->cloneWithWidth($baseWidth)->url().' 1x',
            $this->cloneWithWidth($baseWidth * 2)->url().' 2x',
        ]);
    }

    /** Clone this instance with a specific width, preserving all other transforms. */
    private function cloneWithWidth(int $width): self
    {
        $this->assertValidDimension($width, self::DIMENSION_MIN, self::DIMENSION_MAX, 'Width');

        $clone = clone $this;
        $clone->transforms['w'] = (string) $width;

        return $clone;
    }

    private function buildBaseUrl(): string
    {
        return "https://{$this->domain}/{$this->path}";
    }

    private function buildTransformUrl(): string
    {
        // Deferred validation: zoom requires gravity=face (catches edge case where gravity is set after zoom)
        if (isset($this->transforms['zoom']) && ($this->transforms['gravity'] ?? null) !== 'face') {
            throw InvalidTransformParameterException::missingPrerequisite('Zoom', 'gravity=face');
        }

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
        if ($quality instanceof Quality) {
            return $this->with($key, $quality->value);
        }

        if ($quality < self::QUALITY_MIN || $quality > self::QUALITY_MAX) {
            throw InvalidTransformParameterException::outOfRange('Quality', self::QUALITY_MIN, self::QUALITY_MAX);
        }

        return $this->with($key, $quality);
    }

    protected function with(string $key, bool|float|int|string $value): self
    {
        $this->transforms[$key] = strval($value);

        return $this;
    }
}
