<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

interface ContextInterface extends
    InfoPartInterface,
    BasePartInterface,
    MessagePartInterface,
    SenderPartInterface,
    ReceiverPartInterface,
    SpawnPartInterface,
    StopperPartInterface,
    ExtensionPartInterface
{
}
