<?php

declare(strict_types=1);

namespace Phluxor\Router\Broadcast;

use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Phluxor\Router\BroadcastRouterState;
use Phluxor\Router\Config;
use Phluxor\Router\InitProducer;
use Phluxor\Router\StateInterface;

class GroupRouter extends \Phluxor\Router\GroupRouter
{
    public function __construct(
        RefSet $routees
    ) {
        parent::__construct($routees);
    }

    /**
     * @param Ref ...$routees
     * @return Props
     */
    public static function create(Ref ...$routees): Props
    {
        return Props::fromProducer(new InitProducer())
            ->configure(
                Props::withSpawnFunc(
                    Config::spawner(new self(new RefSet(...$routees)))
                )
            );
    }

    public function createRouterState(): StateInterface
    {
        return new BroadcastRouterState();
    }
}
