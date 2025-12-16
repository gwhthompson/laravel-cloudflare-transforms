<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Contracts;

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use InvalidArgumentException;
use Stringable;

/**
 * Contract for Cloudflare Image transformation builders.
 *
 * Implemented by CloudflareImage (actual transformations) and NullCloudflareImage (no-op fallback).
 * Provides type-safe fluent API for all Cloudflare Image Transformation parameters.
 *
 * Note: Methods marked with @throws only throw in CloudflareImage. NullCloudflareImage intentionally
 * skips validation for graceful degradation on non-Cloudflare environments.
 *
 * @see https://developers.cloudflare.com/images/transform-images/transform-via-url/
 */
interface CloudflareImageContract extends Stringable
{
    /** Whether to preserve animation frames. Default true. */
    public function anim(bool $preserve = true): self;

    /** Background color for transparent images (e.g., PNG) and images resized with fit=pad. */
    public function background(string $color): self;

    /**
     * Blur radius between 1 (slight blur) and 250 (maximum).
     *
     * @throws InvalidArgumentException When blur is outside valid range (CloudflareImage only)
     */
    public function blur(float $blur): self;

    /**
     * Brightness multiplier. 1.0 = no change, 0.5 = half brightness, 2.0 = twice as bright.
     *
     * @throws InvalidArgumentException When brightness is outside valid range (CloudflareImage only)
     */
    public function brightness(float $brightness): self;

    /**
     * Compression mode. Only 'fast' is currently supported.
     *
     * @throws InvalidArgumentException When compression is not 'fast' (CloudflareImage only)
     */
    public function compression(string $compression = 'fast'): self;

    /**
     * Contrast multiplier. 1.0 = no change, 0.5 = less contrast, 2.0 = more contrast.
     *
     * @throws InvalidArgumentException When contrast is outside valid range (CloudflareImage only)
     */
    public function contrast(float $contrast): self;

    /**
     * Device pixel ratio multiplier for responsive images (0.1-5).
     *
     * @throws InvalidArgumentException When DPR is outside valid range (CloudflareImage only)
     */
    public function dpr(float $dpr): self;

    /** How to resize the image within the given dimensions. */
    public function fit(Fit $fit): self;

    /** Flip the image horizontally, vertically, or both. */
    public function flip(Flip $flip): self;

    /** Output format. Use Format::Auto for WebP/AVIF in supported browsers. */
    public function format(Format $format): self;

    /**
     * Gamma correction. 1.0 = no change.
     *
     * @throws InvalidArgumentException When gamma is outside valid range (CloudflareImage only)
     */
    public function gamma(float $gamma): self;

    /**
     * Focal point for cropping. Accepts Gravity enum or "XxY" coordinates (0.0-1.0).
     *
     * @throws InvalidArgumentException When gravity format is invalid (CloudflareImage only)
     */
    public function gravity(Gravity|string $gravity): self;

    /** Convert image to grayscale. Convenience method for saturation(0). */
    public function grayscale(): self;

    /**
     * Maximum height in pixels (1-12,000). Behavior depends on fit mode.
     *
     * @throws InvalidArgumentException When height is outside valid range (CloudflareImage only)
     */
    public function height(int $height): self;

    /** Control EXIF metadata preservation in output. */
    public function metadata(Metadata $metadata): self;

    /**
     * Error handling behavior. Only 'redirect' is currently supported.
     *
     * @throws InvalidArgumentException When action is not 'redirect' (CloudflareImage only)
     */
    public function onerror(string $action = 'redirect'): self;

    /** Convenience method: format(Auto) + quality(High). */
    public function optimize(): self;

    /**
     * Quality for JPEG, WebP, and AVIF formats (1-100 or Quality enum).
     *
     * @throws InvalidArgumentException When quality is outside valid range (CloudflareImage only)
     */
    public function quality(Quality|int $quality): self;

    /** Convenience method: width + dpr + format(Auto). */
    public function responsive(int $width, float $dpr = 1): self;

    /**
     * Rotate image by 90, 180, or 270 degrees.
     *
     * @throws InvalidArgumentException When degrees is not 90, 180, or 270 (CloudflareImage only)
     */
    public function rotate(int $degrees): self;

    /**
     * Saturation level. 1.0 = no change, 0 = grayscale, 2.0 = double saturation.
     *
     * @throws InvalidArgumentException When saturation is outside valid range (CloudflareImage only)
     */
    public function saturation(float $saturation): self;

    /**
     * Automatically isolate the subject by replacing background with transparency.
     *
     * Only 'foreground' mode is currently supported.
     *
     * @throws InvalidArgumentException When mode is not 'foreground' (CloudflareImage only)
     */
    public function segment(string $mode = 'foreground'): self;

    /**
     * Sharpen intensity (0-10).
     *
     * @throws InvalidArgumentException When sharpen is outside valid range (CloudflareImage only)
     */
    public function sharpen(float $sharpen): self;

    /**
     * Alternative quality for slow network connections.
     *
     * Triggered when RTT >150ms, save-data enabled, ECT is 2g/3g, or downlink <5Mbps.
     *
     * @throws InvalidArgumentException When quality is outside valid range (CloudflareImage only)
     */
    public function slowConnectionQuality(Quality|int $quality): self;

    /**
     * Generate srcset string with width descriptors for responsive layouts.
     *
     * @param  array<int>  $widths  Width breakpoints (e.g., [320, 640, 960, 1280])
     * @return string Srcset value: "url 320w, url 640w, ..."
     *
     * @throws InvalidArgumentException When widths array is empty (CloudflareImage only)
     *
     * @see https://developers.cloudflare.com/images/transform-images/make-responsive-images/
     */
    public function srcset(array $widths): string;

    /**
     * Generate srcset string with density descriptors for high-DPI displays.
     *
     * @param  int  $baseWidth  The 1x width (will generate 1x and 2x versions)
     * @return string Srcset value: "url 1x, url 2x"
     *
     * @throws InvalidArgumentException When baseWidth would cause 2x to exceed max (CloudflareImage only)
     *
     * @see https://developers.cloudflare.com/images/transform-images/make-responsive-images/
     */
    public function srcsetDensity(int $baseWidth): string;

    /** Convenience method: square thumbnail with cover fit. */
    public function thumbnail(int $size = 150): self;

    /**
     * Trim pixels from edges. Values are top, right, bottom, left.
     *
     * @throws InvalidArgumentException When any value is negative (CloudflareImage only)
     */
    public function trim(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self;

    /**
     * Automatic border removal based on color detection.
     *
     * @param  string|null  $color  Border color to detect (default: auto-detect)
     * @param  int|null  $tolerance  Color matching tolerance (0-255)
     * @param  int|null  $keep  Pixels to keep from detected border
     *
     * @throws InvalidArgumentException When tolerance is outside valid range (CloudflareImage only)
     */
    public function trimBorder(?string $color = null, ?int $tolerance = null, ?int $keep = null): self;

    /**
     * Generate the final Cloudflare transformation URL.
     *
     * @throws InvalidArgumentException When path is invalid or file doesn't exist (CloudflareImage only)
     */
    public function url(): string;

    /**
     * Maximum width in pixels (1-12,000). Use auto=true for automatic responsive sizing.
     *
     * @throws InvalidArgumentException When width is outside valid range (CloudflareImage only)
     */
    public function width(int $width = 640, bool $auto = false): self;

    /**
     * Zoom level for face-focused crops (0-1). Requires gravity=face.
     *
     * @throws InvalidArgumentException When zoom is outside valid range or gravity is not 'face' (CloudflareImage only)
     */
    public function zoom(float $zoom): self;
}
