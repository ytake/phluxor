<?php

namespace Phluxor\ActorSystem\Context;

interface ReceiverInterface extends
    InfoPartInterface,
    ReceiverPartInterface,
    SenderPartInterface,
    MessagePartInterface
{
}
