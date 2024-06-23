<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use Phluxor\ActorSystem;
use Phluxor\Metrics\ActorMetrics;
use Phluxor\Metrics\PhluxorMetrics;
use Phluxor\Value\ContextExtensionId;
use Phluxor\Value\ExtensionInterface;

/**
 * The metrics extension.
 */
class Metrics implements ExtensionInterface
{
    private ContextExtensionId $extensionId;
    private ?PhluxorMetrics $metrics;
    private bool $enabled = false;

    /**
     * @param ActorSystem $actorSystem
     * @param MeterProviderInterface|null $meterProvider
     */
    public function __construct(
        ActorSystem $actorSystem,
        ?MeterProviderInterface $meterProvider = null
    ) {
        $this->extensionId = new ContextExtensionId();
        if ($meterProvider !== null) {
            $this->metrics = new PhluxorMetrics($actorSystem->getLogger());
            $this->enabled = true;
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function extensionID(): ContextExtensionId
    {
        return $this->extensionId;
    }

    /**
     * @param Closure(ObserverInterface): void $closure
     * @return void
     */
    public function prepareMailboxLengthGauge(Closure $closure): void
    {
        $meter = Globals::meterProvider()->getMeter(ActorMetrics::METRICS_NAME);
        $this->metrics->instruments()
            ->registerActorMailboxLengthGauge(
                $meter->createObservableGauge(
                    'phluxor_actor_mailbox_length',
                    '1',
                    'actor mailbox length',
                    [],
                    callbacks: $closure
                )
            );
    }

    /**
     * @param Context\ContextInterface $context
     * @return AttributesInterface
     */
    public function commonLabels(
        ActorSystem\Context\ContextInterface $context
    ): AttributesInterface {
        return Attributes::create([
            'address' => $context->actorSystem()->address(),
            'actor' => get_class($context->actor()),
        ]);
    }

    public function metrics(): ?PhluxorMetrics
    {
        return $this->metrics;
    }
}
