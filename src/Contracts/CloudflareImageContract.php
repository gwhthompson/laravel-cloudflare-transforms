<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Contracts;

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Stringable;

/**
 * Contract for Cloudflare Image transformation builders.
 *
 * Implemented by CloudflareImage (actual transformations) and NullCloudflareImage (no-op fallback).
 * Provides type-safe fluent API for all Cloudflare Image Transformation parameters.
 *
 * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/
 */
interface CloudflareImageContract extends Stringable
{
    /** Whether to preserve animation frames. Default true. */
    public function anim(bool $preserve = true): self;

    /** Background color for transparent images (e.g., PNG) and images resized with fit=pad. */
    public function background(string $color): self;

    /** Blur radius between 1 (slight blur) and 250 (maximum). */
    public function blur(float $blur): self;

    /**
     * Add a border around the image.
     *
     * Use either $width for uniform border, or $top/$right/$bottom/$left for individual sides.
     * Border is added after resizing and accounts for DPR.
     */
    public function border(
        ?string $color = null,
        ?int $width = null,
        ?int $top = null,
        ?int $right = null,
        ?int $bottom = null,
        ?int $left = null
    ): self;

    /** Brightness multiplier. 1.0 = no change, 0.5 = half brightness, 2.0 = twice as bright. */
    public function brightness(float $brightness): self;

    /** Compression mode. Only 'fast' is currently supported. */
    public function compression(string $compression = 'fast'): self;

    /** Contrast multiplier. 1.0 = no change, 0.5 = less contrast, 2.0 = more contrast. */
    public function contrast(float $contrast): self;

    /** Device pixel ratio multiplier for responsive images (0.1-5). */
    public function dpr(float $dpr): self;

    /** How to resize the image within the given dimensions. */
    public function fit(Fit $fit): self;

    /** Flip the image horizontally, vertically, or both. */
    public function flip(Flip $flip): self;

    /** Output format. Use Format::Auto for WebP/AVIF in supported browsers. */
    public function format(Format $format): self;

    /** Gamma correction. 1.0 = no change. */
    public function gamma(float $gamma): self;

    /** Focal point for cropping when used with fit=cover or fit=crop. */
    public function gravity(Gravity|string $gravity): self;

    /** Convert image to grayscale. Convenience method for saturation(0). */
    public function grayscale(): self;

    /** Maximum height in pixels (1-12,000). Behavior depends on fit mode. */
    public function height(int $height): self;

    /** Control EXIF metadata preservation in output. */
    public function metadata(Metadata $metadata): self;

    /** Error handling behavior. Only 'redirect' is currently supported. */
    public function onerror(string $action = 'redirect'): self;

    /** Convenience method: format(Auto) + quality(High). */
    public function optimize(): self;

    /** Quality for JPEG, WebP, and AVIF formats (1-100 or Quality enum). */
    public function quality(Quality|int $quality): self;

    /** Convenience method: width + dpr + format(Auto). */
    public function responsive(int $width, float $dpr = 1): self;

    /** Rotate image by 90, 180, or 270 degrees. */
    public function rotate(int $degrees): self;

    /** Saturation level. 1.0 = no change, 0 = grayscale, 2.0 = double saturation. */
    public function saturation(float $saturation): self;

    /**
     * Automatically isolate the subject by replacing background with transparency.
     *
     * Only 'foreground' mode is currently supported.
     */
    public function segment(string $mode = 'foreground'): self;

    /** Sharpen intensity (0-10). */
    public function sharpen(float $sharpen): self;

    /**
     * Alternative quality for slow network connections.
     *
     * Triggered when RTT >150ms, save-data enabled, ECT is 2g/3g, or downlink <5Mbps.
     */
    public function slowConnectionQuality(Quality|int $quality): self;

    /** Convenience method: square thumbnail with cover fit. */
    public function thumbnail(int $size = 150): self;

    /** Trim pixels from edges. Values are top, right, bottom, left. */
    public function trim(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self;

    /**
     * Automatic border removal based on color detection.
     *
     * @param  string|null  $color  Border color to detect (default: auto-detect)
     * @param  int|null  $tolerance  Color matching tolerance (0-255)
     * @param  int|null  $keep  Pixels to keep from detected border
     */
    public function trimBorder(?string $color = null, ?int $tolerance = null, ?int $keep = null): self;

    /** Generate the final Cloudflare transformation URL. */
    public function url(): string;

    /** Maximum width in pixels (1-12,000). Use auto=true for automatic responsive sizing. */
    public function width(int $width = 640, bool $auto = false): self;

    /** Zoom level for face-focused crops (0-1). Requires gravity=face. */
    public function zoom(float $zoom): self;
}
