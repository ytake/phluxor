<?php

declare(strict_types=1);

namespace Phluxor\Router;

enum RouterType: int
{
    case GroupRouterType = 0;
    case PoolRouterType = 1;
}
