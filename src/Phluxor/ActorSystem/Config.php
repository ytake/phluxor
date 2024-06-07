<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Message\ActorInterface;

readonly class Config
{
    /**
     * @param int $deadLetterThrottleInterval
     * @param int $deadLetterThrottleCount
     * @param bool $deadLetterRequestLogging
     * @param bool $developerSupervisionLogging
     * @param Closure(ActorInterface): string|null $diagnosticsSerializer
     * @param ActorSystem\Logger\LoggerInterface|null $loggerFactory
     * @param null $metricsProvider
     */
    public function __construct(
        private int $deadLetterThrottleInterval = 1,
        private int $deadLetterThrottleCount = 3,
        private bool $deadLetterRequestLogging = true,
        private bool $developerSupervisionLogging = false,
        private Closure|null $diagnosticsSerializer = null,
        private ActorSystem\Logger\LoggerInterface|null $loggerFactory = null,
        private null $metricsProvider = null,
    ) {
    }

    public function deadLetterThrottleInterval(): int
    {
        return $this->deadLetterThrottleInterval;
    }

    public function deadLetterThrottleCount(): int
    {
        return $this->deadLetterThrottleCount;
    }

    public function deadLetterRequestLogging(): bool
    {
        return $this->deadLetterRequestLogging;
    }

    public function developerSupervisionLogging(): bool
    {
        return $this->developerSupervisionLogging;
    }

    /**
     * @return Closure(ActorInterface): string
     */
    public function diagnosticsSerializer(): Closure
    {
        if ($this->diagnosticsSerializer !== null) {
            return $this->diagnosticsSerializer;
        }
        return fn(ActorInterface $actor): string => "";
    }

    /**
     * @return ActorSystem\Logger\LoggerInterface
     */
    public function loggerFactory(): ActorSystem\Logger\LoggerInterface
    {
        if ($this->loggerFactory !== null) {
            return $this->loggerFactory;
        }
        return new ActorSystem\Logger\StdoutLogger();
    }
}
