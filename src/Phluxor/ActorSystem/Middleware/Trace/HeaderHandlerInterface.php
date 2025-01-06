<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Middleware\Trace;

use Exception;

interface HeaderHandlerInterface
{
    /**
     * @param string $key
     * @param string $val
     * @return Exception|null
     */
    public function __invoke(string $key, string $val): ?Exception;
}
