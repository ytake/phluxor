<?php

declare(strict_types=1);

namespace Phluxor\Swoole\Coroutine;

/**
 * @param callable $fn
 * @param mixed ...$args
 */
function run(callable $fn, ...$args): void
{
    if (extension_loaded('swoole')) {
        \Swoole\Coroutine\run($fn, ...$args); // @phpstan-ignore-line
        return;
    }
    if (extension_loaded('openswoole')) {
        \Co::run($fn, ...$args); // @phpstan-ignore-line
        return;
    }
}
