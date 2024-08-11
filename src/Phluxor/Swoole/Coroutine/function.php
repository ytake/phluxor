<?php

declare(strict_types=1);

namespace Phluxor\Swoole\Coroutine;

/**
 * @param callable $fn
 * @param mixed ...$args
 * @return bool
 */
function run(callable $fn, ...$args): bool
{
    if (extension_loaded('swoole')) {
        return \Swoole\Coroutine\run($fn, ...$args); // @phpstan-ignore-line
    }
    if (extension_loaded('openswoole')) {
        return \OpenSwoole\Coroutine::run($fn, ...$args); // @phpstan-ignore-line
    }
}

/**
 * @param callable $fn
 * @param mixed ...$args
 * @return mixed
 */
function go(callable $fn, ...$args): mixed
{
    if (extension_loaded('swoole')) {
        return \Swoole\Coroutine\go($fn, ...$args); // @phpstan-ignore-line
    }
    if (extension_loaded('openswoole')) {
        return \OpenSwoole\Coroutine::go($fn, ...$args); // @phpstan-ignore-line
    }
}
