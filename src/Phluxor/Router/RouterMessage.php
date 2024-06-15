<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\Router\Message\Broadcast;
use Phluxor\Router\ProtoBuf\AddRoutee;
use Phluxor\Router\ProtoBuf\AdjustPoolSize;
use Phluxor\Router\ProtoBuf\GetRoutees;
use Phluxor\Router\ProtoBuf\RemoveRoutee;

readonly class RouterMessage
{
    public function __construct(
        private mixed $o
    ) {
    }

    /**
     * @return bool
     */
    public function isManagementMessage(): bool
    {
        return match (true) {
            $this->o instanceof AddRoutee,
                $this->o instanceof RemoveRoutee,
                $this->o instanceof GetRoutees,
                $this->o instanceof AdjustPoolSize,
                $this->o instanceof Broadcast => true,
            default => false,
        };
    }
}
