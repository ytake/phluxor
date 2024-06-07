<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Props;

use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;

interface ReceiverMiddlewareInterface
{
    /**
     * @param ReceiverFunctionInterface $next
     * @return ReceiverMiddlewareInterface
     */
    public function __invoke(ReceiverFunctionInterface $next): ReceiverMiddlewareInterface;
}
