<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

enum Valve: int
{
    case Open = 0;
    case Closing = 1;
    case Closed = 2;
}
