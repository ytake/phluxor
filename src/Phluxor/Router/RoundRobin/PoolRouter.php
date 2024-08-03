<?php

declare(strict_types=1);

namespace Phluxor\Router\RoundRobin;

use Closure;
use Phluxor\ActorSystem\Props;
use Phluxor\Router\Config;
use Phluxor\Router\InitProducer;
use Phluxor\Router\RoundRobinState;
use Phluxor\Router\StateInterface;

class PoolRouter extends \Phluxor\Router\PoolRouter
{
    public function __construct(
        int $poolSize
    ) {
        parent::__construct($poolSize);
    }

    /**
     * @param int $poolSize
     * @param Closure(Props): void ...$options
     * @return Props
     */
    public static function create(int $poolSize, Closure ...$options): Props
    {
        return Props::fromProducer(new InitProducer())
            ->configure(Props::withSpawnFunc(Config::spawner(new self($poolSize))))
            ->configure(...$options);
    }

    public function createRouterState(): StateInterface
    {
        return new RoundRobinState();
    }
}
