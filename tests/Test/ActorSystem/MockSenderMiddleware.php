<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Closure;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;

class MockSenderMiddleware implements SenderMiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(Closure|SenderFunctionInterface $next): SenderFunctionInterface
    {
        return new MockSenderFunction($next);
    }
}
