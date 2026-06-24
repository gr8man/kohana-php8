<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/application/classes',
        __DIR__ . '/modules',
        __DIR__ . '/system/classes',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        __DIR__ . '/system/tests',
        __DIR__ . '/modules/*/tests',
        __DIR__ . '/application/tests',
        // Keep Kohana-style long arrays to match php-cs-fixer
        \Rector\Php54\Rector\Array_\LongArrayToShortArrayRector::class,
    ])
    ->withPhpSets(
        php82: true
    )
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true
    )
    ->withIndent("\t", 1);
