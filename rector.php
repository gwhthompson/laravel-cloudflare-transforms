<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\TypedCollections\Rector\ClassMethod\DefaultCollectionKeyRector;
use RectorLaravel\Rector\ClassMethod\AddGenericReturnTypeToRelationsRector;
use RectorLaravel\Rector\MethodCall\EloquentWhereTypeHintClosureParameterRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withRules([
        DefaultCollectionKeyRector::class,
        EloquentWhereTypeHintClosureParameterRector::class,
        AddGenericReturnTypeToRelationsRector::class,
    ])
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
    ])
    ->withPhpSets(php84: true)
    ->withAttributesSets()
    ->withImportNames()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
        carbon: true,
        rectorPreset: true,
    )
    ->withSkip([
        EncapsedStringsToSprintfRector::class,
        SeparateMultiUseImportsRector::class,
    ]);
