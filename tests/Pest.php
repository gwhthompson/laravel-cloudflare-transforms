<?php

declare(strict_types=1);

uses(Gwhthompson\CloudflareTransforms\Tests\TestCase::class)->in('Feature');

// Unit tests also need Laravel's TestCase for CloudflareImage config access
uses(Gwhthompson\CloudflareTransforms\Tests\TestCase::class)->in('Unit');