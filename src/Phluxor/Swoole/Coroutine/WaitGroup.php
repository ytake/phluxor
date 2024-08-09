<?php

declare(strict_types=1);

namespace Phluxor\Swoole\Coroutine;

/**
 * @method void add(int $delta = 1)
 * @method void done()
 * @method bool wait(float $timeout = -1)
 * @method int count()
 */
class WaitGroup
{
    private \Co\WaitGroup|\OpenSwoole\Core\Coroutine\WaitGroup $wg; // @phpstan-ignore-line

    public function __construct(int $delta = 0)
    {
        if (extension_loaded('swoole')) {
            $this->wg = new \Swoole\Coroutine\WaitGroup($delta);  // @phpstan-ignore-line
        } elseif (extension_loaded('openswoole')) {
            $this->wg = new \OpenSwoole\Core\Coroutine\WaitGroup($delta); // @phpstan-ignore-line
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed // @phpstan-ignore-line
    {
        return $this->wg->$name(...$arguments);
    }
}
