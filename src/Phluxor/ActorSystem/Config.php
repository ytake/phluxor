<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Message\ActorInterface;

class Config
{
    /** use this to configure the actor system
     *  <code>
     *     $config = new Config();
     *     $config->setMetricsProvider(new MyMetricsProvider());
     *     \Phluxor\ActorSystem::create($config);
     * </code>
     * @param int $deadLetterThrottleInterval
     * @param int $deadLetterThrottleCount
     * @param bool $deadLetterRequestLogging
     * @param bool $developerSupervisionLogging
     * @param Closure(ActorInterface): string|null $diagnosticsSerializer
     * @param ActorSystem\Logger\LoggerInterface|null $loggerFactory
     * @param null|MeterProviderInterface $metricsProvider
     */
    public function __construct(
        private int $deadLetterThrottleInterval = 1,
        private int $deadLetterThrottleCount = 3,
        private bool $deadLetterRequestLogging = true,
        private bool $developerSupervisionLogging = false,
        private Closure|null $diagnosticsSerializer = null,
        private ActorSystem\Logger\LoggerInterface|null $loggerFactory = null,
        private ?MeterProviderInterface $metricsProvider = null,
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

    /**
     * @return MeterProviderInterface|null
     */
    public function metricsProvider(): ?MeterProviderInterface
    {
        return $this->metricsProvider;
    }

    public function setDeadLetterThrottleInterval(int $deadLetterThrottleInterval): Config
    {
        $this->deadLetterThrottleInterval = $deadLetterThrottleInterval;
        return $this;
    }

    public function setDeadLetterThrottleCount(int $deadLetterThrottleCount): Config
    {
        $this->deadLetterThrottleCount = $deadLetterThrottleCount;
        return $this;
    }

    public function setDeadLetterRequestLogging(bool $deadLetterRequestLogging): Config
    {
        $this->deadLetterRequestLogging = $deadLetterRequestLogging;
        return $this;
    }

    public function setDeveloperSupervisionLogging(bool $developerSupervisionLogging): Config
    {
        $this->developerSupervisionLogging = $developerSupervisionLogging;
        return $this;
    }

    public function setDiagnosticsSerializer(Closure $diagnosticsSerializer): Config
    {
        $this->diagnosticsSerializer = $diagnosticsSerializer;
        return $this;
    }

    /**
     * if you want to use a custom logger factory
     * default is @see \Phluxor\ActorSystem\Logger\StdoutLogger
     * @param Logger\LoggerInterface $loggerFactory
     * @return $this
     */
    public function setLoggerFactory(ActorSystem\Logger\LoggerInterface $loggerFactory): Config
    {
        $this->loggerFactory = $loggerFactory;
        return $this;
    }

    /**
     * open telemetry metrics
     * @param ActorSystem\Metrics\ProviderInterface $metricsProvider
     * @return Config
     */
    public function setMetricsProvider(
        ActorSystem\Metrics\ProviderInterface $metricsProvider
    ): Config {
        $this->metricsProvider = $metricsProvider->provide();
        return $this;
    }
}
