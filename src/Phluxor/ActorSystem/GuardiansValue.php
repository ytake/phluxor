<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem;

use function spl_object_hash;

class GuardiansValue
{
    /** @var GuardianProcess[] array<string, GuardianProcess> */
    private array $guardians = [];

    /**
     * @param ActorSystem $actorSystem
     */
    public function __construct(
        private readonly ActorSystem $actorSystem
    ) {
    }

    public function getGuardianRef(SupervisorStrategyInterface $strategy): Ref
    {
        $key = $this->getKeyForStrategy($strategy);
        if (isset($this->guardians[$key])) {
            return $this->guardians[$key]->getRef();
        }

        $guardian = $this->makeGuardian($strategy);
        $this->guardians[$key] = $guardian;
        return $guardian->getRef();
    }

    private function makeGuardian(SupervisorStrategyInterface $strategy): GuardianProcess
    {
        $ref = new GuardianProcess(
            guardiansValue: $this,
            pid: null,
            strategy: $strategy,
        );
        $id = $this->actorSystem->getProcessRegistry()->nextId();
        $pid = $this->actorSystem->getProcessRegistry()->add($ref, "guardian" . $id);

        if (!$pid->isAdded()) {
            $this->actorSystem->getLogger()->error(
                "Failed to register guardian process",
                ['pid' => $pid->getRef()]
            );
        }
        $ref->setRef($pid->getRef());
        return $ref;
    }

    private function getKeyForStrategy(
        SupervisorStrategyInterface $strategy
    ): string {
        return spl_object_hash($strategy);
    }

    /**
     * @return ActorSystem
     */
    public function getActorSystem(): ActorSystem
    {
        return $this->actorSystem;
    }
}
