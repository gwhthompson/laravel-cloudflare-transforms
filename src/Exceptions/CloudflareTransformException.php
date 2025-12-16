<?php

declare(strict_types=1);

namespace Gwhthompson\CloudflareTransforms\Exceptions;

use RuntimeException;

/**
 * Base exception class for all Cloudflare Transform exceptions.
 *
 * Allows catching all package-specific exceptions with a single catch block.
 */
class CloudflareTransformException extends RuntimeException {}
