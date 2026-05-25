<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->disableParallel();

    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/routes',
        __DIR__ . '/cli',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/storage',
        __DIR__ . '/public',
        __DIR__ . '/src/views',
    ]);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
    ]);
};
