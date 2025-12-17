<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\View\Components;

use Gwhthompson\CloudflareTransforms\Contracts\CloudflareImageContract;
use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Quality;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

/**
 * Blade component for rendering Cloudflare-transformed images with srcset support.
 *
 * @see https://developers.cloudflare.com/images/transform-images/make-responsive-images/
 */
class Image extends Component
{
    public string $disk;

    /** @param array<int>|null $srcset */
    public function __construct(
        public string $path,
        ?string $disk = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?Format $format = null,
        public ?Fit $fit = null,
        public Gravity|string|null $gravity = null,
        public Quality|int|null $quality = null,
        public ?array $srcset = null,
        public ?int $srcsetDensity = null,
        public ?string $sizes = null,
    ) {
        $diskConfig = Config::get('cloudflare-transforms.disk', 'public');
        $this->disk = $disk ?? (is_string($diskConfig) ? $diskConfig : 'public');
    }

    public function render(): View
    {
        return view('cloudflare::components.image');
    }

    /** Build the CloudflareImage instance with applied transforms. */
    public function imageBuilder(): CloudflareImageContract
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->disk);

        /** @var CloudflareImageContract $image */
        $image = $disk->image($this->path); // @phpstan-ignore method.notFound

        if ($this->width !== null) {
            $image = $image->width($this->width);
        }

        if ($this->height !== null) {
            $image = $image->height($this->height);
        }

        if ($this->format !== null) {
            $image = $image->format($this->format);
        }

        if ($this->fit !== null) {
            $image = $image->fit($this->fit);
        }

        if ($this->gravity !== null) {
            $image = $image->gravity($this->gravity);
        }

        if ($this->quality !== null) {
            $image = $image->quality($this->quality);
        }

        return $image;
    }

    /** Get the src attribute value. Uses largest srcset width as fallback. */
    public function srcAttribute(): string
    {
        $image = $this->imageBuilder();

        // Default src uses largest srcset width (Cloudflare recommendation: don't scale up)
        if ($this->srcset !== null && $this->srcset !== []) {
            $image = $image->width(max($this->srcset));
        } elseif ($this->srcsetDensity !== null) {
            // For density srcset, use 2x as the src (highest quality fallback)
            $image = $image->width($this->srcsetDensity * 2);
        }

        return $image->url();
    }

    /** Get the srcset attribute value, or null if not configured. */
    public function srcsetAttribute(): ?string
    {
        if ($this->srcset !== null && $this->srcset !== []) {
            return $this->imageBuilder()->srcset($this->srcset);
        }

        if ($this->srcsetDensity !== null) {
            return $this->imageBuilder()->srcsetDensity($this->srcsetDensity);
        }

        return null;
    }
}
