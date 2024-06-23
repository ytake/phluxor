<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Metrics;

use Phluxor\ActorSystem;

trait MetricsSystemTrait
{
    /**
     * Checks if the given ActorSystem has a Metrics extension enabled and returns it.
     *
     * @param ActorSystem $actorSystem The ActorSystem to check.
     * @return ActorSystem\Metrics|null The Metrics extension if found and enabled, null otherwise.
     */
    protected function enabledMetricsSystem(ActorSystem $actorSystem): ?ActorSystem\Metrics
    {
        $extensionId = $actorSystem->metrics()?->extensionID();
        if ($extensionId) {
            $metricsSystem = $actorSystem->extensions()->get($extensionId);
            if ($metricsSystem instanceof ActorSystem\Metrics && $metricsSystem->isEnabled()) {
                return $metricsSystem;
            }
        }
        return null;
    }
}
