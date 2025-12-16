<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Exceptions;

/**
 * Exception thrown when a file does not exist on the configured storage disk.
 */
class FileNotFoundException extends CloudflareTransformException
{
    /** Create an exception for a file that doesn't exist. */
    public static function forPath(string $path, ?string $disk = null): self
    {
        $message = $disk !== null
            ? sprintf('File does not exist on disk "%s": %s', $disk, $path)
            : sprintf('File does not exist: %s', $path);

        return new self($message);
    }
}
