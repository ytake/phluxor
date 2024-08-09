<?php

declare(strict_types=1);

namespace Phluxor\Swoole\OpenTelemetry;

use OpenTelemetry\Context\ExecutionContextAwareInterface;

final readonly class SwooleContextDestructor
{
    public function __construct(
        private ExecutionContextAwareInterface $storage,
        private int $cid
    ) {
    }

    public function __destruct()
    {
        $this->storage->destroy($this->cid);
    }
}
