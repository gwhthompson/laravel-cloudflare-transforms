<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Exceptions;

/**
 * Exception thrown when a transformation parameter is invalid.
 *
 * This includes out-of-range values, invalid types, and constraint violations.
 */
class InvalidTransformParameterException extends CloudflareTransformException
{
    /** Create an exception for a value outside the allowed range. */
    public static function outOfRange(string $parameter, int|float $min, int|float $max): self
    {
        return new self(sprintf('%s must be between %s and %s', $parameter, (string) $min, (string) $max));
    }

    /**
     * Create an exception for a value not in an allowed set.
     *
     * @param  array<int|string>  $allowed
     */
    public static function notInSet(string $parameter, array $allowed): self
    {
        return new self(sprintf('%s must be one of: %s', $parameter, implode(', ', $allowed)));
    }

    /** Create an exception for an invalid path. */
    public static function invalidPath(string $reason = 'Invalid path'): self
    {
        return new self($reason);
    }

    /** Create an exception for a missing prerequisite. */
    public static function missingPrerequisite(string $parameter, string $prerequisite): self
    {
        return new self(sprintf('%s requires %s', $parameter, $prerequisite));
    }
}
