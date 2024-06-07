<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Logger;

use Phluxor\ActorSystem;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

interface LoggerInterface
{
    /**
     * @param ActorSystem $actorSystem
     * @return PsrLoggerInterface
     */
    public function __invoke(ActorSystem $actorSystem): PsrLoggerInterface;
}
