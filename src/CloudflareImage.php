<?php

namespace Gwhthompson\CloudflareTransforms;

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CloudflareImage
{
    /** @var array<string,string> */
    private array $transforms = [];

    private function __construct(
        private readonly string $path,
        private readonly string $domain,
        private readonly string $disk,
        private readonly string $transformPath,
    ) {}

    public function __toString(): string
    {
        return $this->url();
    }

    public function anim(bool $preserve = true): self
    {
        return $this->with('anim', $preserve);
    }

    public function background(string $color): self
    {
        return $this->with('background', $color);
    }

    public function blur(float $blur): self
    {
        return $blur >= 1 && $blur <= 250
            ? $this->with('blur', $blur)
            : throw new InvalidArgumentException('Blur must be 1-250');
    }

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

    public function fit(Fit $fit): self
    {
        return $this->with('fit', $fit->value);
    }

    public function flip(Flip $direction): self
    {
        return $this->with('flip', $direction->value);
    }

    public function format(Format $format): self
    {
        return $this->with('format', $format->value);
    }

    public function gamma(float $gamma): self
    {
        return $gamma >= 0 && $gamma <= 2
            ? $this->with('gamma', $gamma)
            : throw new InvalidArgumentException('Gamma must be 0-2');
    }

    public function gravity(Gravity|string $gravity): self
    {
        $value = $gravity instanceof Gravity ? $gravity->value : $gravity;

        return match (true) {
            $gravity instanceof Gravity => $this->with('gravity', $value),
            preg_match('/^(0(\.\d+)?|1(\.0+)?)x(0(\.\d+)?|1(\.0+)?)$/', $value) => $this->with('gravity', $value),
            default => throw new InvalidArgumentException('Invalid gravity')
        };
    }

    public function grayscale(): self
    {
        return $this->saturation(0);
    }

    public function height(int $height): self
    {
        return $height >= 1 && $height <= 12000
            ? $this->with('height', $height)
            : throw new InvalidArgumentException('Height must be 1-12,000');
    }

    public static function make(
        string $path,
        ?string $domain = null,
        ?string $disk = null,
        ?string $transformPath = null,
    ): self {
        return new self(
            path: $path,
            domain: $domain ?? Config::string('cloudflare-transforms.domain'),
            disk: $disk ?? Config::string('cloudflare-transforms.disk', 'public'),
            transformPath: $transformPath ?? Config::string('cloudflare-transforms.transform_path', 'cdn-cgi/image'),
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

    public function quality(Quality|int $quality): self
    {
        return match (true) {
            $quality instanceof Quality => $this->with('quality', $quality->value),
            $quality >= 1 && $quality <= 100 => $this->with('quality', $quality),
            default => throw new InvalidArgumentException('Invalid quality')
        };
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

    public function url(): string
    {
        if (empty($this->path) || str_contains($this->path, '..')) {
            throw new InvalidArgumentException('Invalid path');
        }

        if (! Storage::disk($this->disk)->exists($this->path)) {
            throw new InvalidArgumentException("File does not exist: {$this->path}");
        }

        $baseUrl = $this->buildBaseUrl();

        return empty($this->transforms)
            ? $baseUrl
            : $this->buildTransformUrl();
    }

    public function width(int $width = 640, bool $auto = false): self
    {
        return match (true) {
            $auto => $this->with('width', 'auto'),
            default => $width >= 1 && $width <= 12000
                ? $this->with('width', $width)
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

    private function buildBaseUrl(): string
    {
        return "https://{$this->domain}/{$this->path}";
    }

    private function buildTransformUrl(): string
    {
        $options = array_map(
            fn ($key, $value) => match ($key) {
                'background' => 'background='.urlencode($value),
                'anim' => 'anim='.($value ? 'true' : 'false'),
                default => "{$key}={$value}"
            },
            array_keys($this->transforms),
            $this->transforms
        );

        return "https://{$this->domain}/{$this->transformPath}/"
            .implode(',', $options)
            ."/{$this->path}";
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
