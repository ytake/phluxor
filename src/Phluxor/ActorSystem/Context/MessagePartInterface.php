<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem\ReadonlyMessageHeaderInterface;

interface MessagePartInterface
{
    public function message(): mixed;

    public function messageHeader(): ReadonlyMessageHeaderInterface;
}
