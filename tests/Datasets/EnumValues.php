<?php

declare(strict_types=1);

use Gwhthompson\CloudflareTransforms\Enums\Fit;
use Gwhthompson\CloudflareTransforms\Enums\Flip;
use Gwhthompson\CloudflareTransforms\Enums\Format;
use Gwhthompson\CloudflareTransforms\Enums\Gravity;
use Gwhthompson\CloudflareTransforms\Enums\Metadata;
use Gwhthompson\CloudflareTransforms\Enums\Quality;

dataset('fit_enum_values', [
    'contain' => [Fit::Contain, 'contain'],
    'cover' => [Fit::Cover, 'cover'],
    'crop' => [Fit::Crop, 'crop'],
    'pad' => [Fit::Pad, 'pad'],
    'scale-down' => [Fit::ScaleDown, 'scale-down'],
    'squeeze' => [Fit::Squeeze, 'squeeze'],
]);

dataset('format_enum_values', [
    'auto' => [Format::Auto, 'auto'],
    'avif' => [Format::Avif, 'avif'],
    'baseline-jpeg' => [Format::BaselineJpeg, 'baseline-jpeg'],
    'jpeg' => [Format::Jpeg, 'jpeg'],
    'json' => [Format::Json, 'json'],
    'webp' => [Format::Webp, 'webp'],
]);

dataset('quality_enum_values', [
    'high' => [Quality::High, 'high'],
    'low' => [Quality::Low, 'low'],
    'medium-high' => [Quality::MediumHigh, 'medium-high'],
    'medium-low' => [Quality::MediumLow, 'medium-low'],
]);

dataset('gravity_enum_values', [
    'auto' => [Gravity::Auto, 'auto'],
    'bottom' => [Gravity::Bottom, 'bottom'],
    'face' => [Gravity::Face, 'face'],
    'left' => [Gravity::Left, 'left'],
    'right' => [Gravity::Right, 'right'],
    'top' => [Gravity::Top, 'top'],
]);

dataset('flip_enum_values', [
    'both' => [Flip::Both, 'hv'],
    'horizontal' => [Flip::Horizontal, 'h'],
    'vertical' => [Flip::Vertical, 'v'],
]);

dataset('metadata_enum_values', [
    'copyright' => [Metadata::Copyright, 'copyright'],
    'keep' => [Metadata::Keep, 'keep'],
    'none' => [Metadata::None, 'none'],
]);

dataset('enum_transformations', [
    'fit_cover' => ['fit', Fit::Cover, 'fit=cover'],
    'format_webp' => ['format', Format::Webp, 'f=webp'],
    'quality_high' => ['quality', Quality::High, 'q=high'],
    'gravity_face' => ['gravity', Gravity::Face, 'gravity=face'],
    'flip_horizontal' => ['flip', Flip::Horizontal, 'flip=h'],
    'metadata_none' => ['metadata', Metadata::None, 'metadata=none'],
]);
