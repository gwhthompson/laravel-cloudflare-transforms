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

/** Null object for non-Cloudflare disks. Returns original URL unchanged. */
final readonly class NullCloudflareImage implements CloudflareImageContract
{
    public function __construct(private string $originalUrl) {}

    public function __toString(): string
    {
        return $this->originalUrl;
    }

    public function anim(bool $preserve = true): self
    {
        return $this;
    }

    public function background(string $color): self
    {
        return $this;
    }

    public function blur(float $blur): self
    {
        return $this;
    }

    public function brightness(float $brightness): self
    {
        return $this;
    }

    public function compression(string $compression = 'fast'): self
    {
        return $this;
    }

    public function contrast(float $contrast): self
    {
        return $this;
    }

    public function dpr(float $dpr): self
    {
        return $this;
    }

    public function fit(Fit $fit): self
    {
        return $this;
    }

    public function flip(Flip $flip): self
    {
        return $this;
    }

    public function format(Format $format): self
    {
        return $this;
    }

    public function gamma(float $gamma): self
    {
        return $this;
    }

    public function gravity(Gravity|string $gravity): self
    {
        return $this;
    }

    public function grayscale(): self
    {
        return $this;
    }

    public function height(int $height): self
    {
        return $this;
    }

    public function metadata(Metadata $metadata): self
    {
        return $this;
    }

    public function onerror(string $action = 'redirect'): self
    {
        return $this;
    }

    public function optimize(): self
    {
        return $this;
    }

    public function quality(Quality|int $quality): self
    {
        return $this;
    }

    public function responsive(int $width, float $dpr = 1): self
    {
        return $this;
    }

    public function rotate(int $degrees): self
    {
        return $this;
    }

    public function saturation(float $saturation): self
    {
        return $this;
    }

    public function segment(string $mode = 'foreground'): self
    {
        return $this;
    }

    public function sharpen(float $sharpen): self
    {
        return $this;
    }

    public function slowConnectionQuality(Quality|int $quality): self
    {
        return $this;
    }

    public function srcset(array $widths): string
    {
        if ($widths === []) {
            return $this->originalUrl;
        }

        return collect($widths)
            ->map(fn (int $w): string => $this->originalUrl." {$w}w")
            ->implode(', ');
    }

    public function srcsetDensity(int $baseWidth): string
    {
        return implode(', ', [
            $this->originalUrl.' 1x',
            $this->originalUrl.' 2x',
        ]);
    }

    public function thumbnail(int $size = 150): self
    {
        return $this;
    }

    public function trim(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self
    {
        return $this;
    }

    public function trimBorder(?string $color = null, ?int $tolerance = null, ?int $keep = null): self
    {
        return $this;
    }

    public function url(): string
    {
        return $this->originalUrl;
    }

    public function width(int $width = 640, bool $auto = false): self
    {
        return $this;
    }

    public function zoom(float $zoom): self
    {
        return $this;
    }
}
