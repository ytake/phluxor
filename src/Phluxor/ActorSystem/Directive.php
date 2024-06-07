<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

enum Directive: int
{
    case Resume = 0;
    case Restart = 1;
    case Stop = 2;
    case Escalate = 3;
}
