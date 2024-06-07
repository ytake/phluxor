<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

interface MessageBatchInterface
{
    public function getMessages(): array;
}
