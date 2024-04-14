<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/src',
//        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
//        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
//        instanceof: true,
        earlyReturn: true,
//        strictBooleans: true,
    )
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withCache(
    // ensure file system caching is used instead of in-memory
        cacheClass: FileCacheStorage::class,

        // specify a path that works locally as well as on CI job runners
        cacheDirectory: '/tmp/rector'
    );
