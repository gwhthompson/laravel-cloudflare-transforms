<?php

declare(strict_types=1);
use Gwhthompson\CloudflareTransforms\Tests\TestCase;

uses(TestCase::class)->in('Feature');

// Unit tests also need Laravel's TestCase for CloudflareImage config access
uses(TestCase::class)->in('Unit');
