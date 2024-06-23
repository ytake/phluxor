<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Metrics;

use OpenTelemetry\SDK\Metrics\MeterProviderInterface;

/**
 * for open telemetry metrics
 * @see https://github.com/open-telemetry/opentelemetry-php
 * @see https://opentelemetry.io/docs/languages/php/
 */
interface ProviderInterface
{
    /**
     * Retrieves the meter provider.
     *
     * @return MeterProviderInterface The meter provider.
     */
    public function provide(): MeterProviderInterface;
}
