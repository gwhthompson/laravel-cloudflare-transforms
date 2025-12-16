<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Concerns;

use Gwhthompson\CloudflareTransforms\Exceptions\InvalidTransformParameterException;

/**
 * Provides parameter validation for Cloudflare image transformation methods.
 *
 * Methods validate and set transforms in one call for fluent usage.
 * Requires the consuming class to implement the `with()` method.
 */
trait ValidatesTransformParameters
{
    /**
     * Set a transform value on the builder.
     *
     * This abstract method must be implemented by the consuming class.
     * It enables the trait to set validated values on the transforms array.
     */
    abstract protected function with(string $key, mixed $value): self;

    /**
     * Validate an integer is within range and set the transform.
     *
     * @throws InvalidTransformParameterException
     */
    private function setValidatedInt(string $key, int $value, int $min, int $max, string $name): self
    {
        if ($value < $min || $value > $max) {
            throw InvalidTransformParameterException::outOfRange($name, $min, $max);
        }

        return $this->with($key, $value);
    }

    /**
     * Validate a float is within range and set the transform.
     *
     * @throws InvalidTransformParameterException
     */
    private function setValidatedFloat(string $key, float $value, float $min, float $max, string $name): self
    {
        if ($value < $min || $value > $max) {
            throw InvalidTransformParameterException::outOfRange($name, $min, $max);
        }

        return $this->with($key, $value);
    }

    /**
     * Validate a value is in an allowed set and set the transform.
     *
     * @param  array<int, int|string>  $allowed
     *
     * @throws InvalidTransformParameterException
     */
    private function setValidatedInSet(string $key, int|string $value, array $allowed, string $name): self
    {
        if (! in_array($value, $allowed, true)) {
            throw InvalidTransformParameterException::notInSet($name, $allowed);
        }

        return $this->with($key, $value);
    }

    /**
     * Validate a value equals expected and set the transform.
     *
     * @throws InvalidTransformParameterException
     */
    private function setValidatedEquals(string $key, string $value, string $expected, string $name): self
    {
        if ($value !== $expected) {
            throw new InvalidTransformParameterException(sprintf('%s must be "%s"', $name, $expected));
        }

        return $this->with($key, $value);
    }

    /**
     * Validate a dimension (width/height) for srcset cloning.
     *
     * Does NOT set - returns validated value for clone operations.
     *
     * @throws InvalidTransformParameterException
     */
    private function assertValidDimension(int $value, int $min, int $max, string $name): int
    {
        if ($value < $min || $value > $max) {
            throw InvalidTransformParameterException::outOfRange($name, $min, $max);
        }

        return $value;
    }
}
