<?php

declare(strict_types=1);

namespace Phluxor\Swoole\Coroutine;

/**
 * @param callable $fn
 * @param mixed ...$args
 * @return void
 */
function run(callable $fn, ...$args): void
{
    if (extension_loaded('swoole')) {
        \Swoole\Coroutine\run($fn, ...$args); // @phpstan-ignore-line
        return;
    }
    if (extension_loaded('openswoole')) {
        \OpenSwoole\Coroutine::run($fn, ...$args); // @phpstan-ignore-line
        return;
    }
}

/**
 * @param callable $fn
 * @param mixed ...$args
 * @return void
 */
function go(callable $fn, ...$args): void
{
    if (extension_loaded('swoole')) {
        \Swoole\Coroutine\go($fn, ...$args); // @phpstan-ignore-line
        return;
    }
    if (extension_loaded('openswoole')) {
        \OpenSwoole\Coroutine::go($fn, ...$args); // @phpstan-ignore-line
        return;
    }
}
