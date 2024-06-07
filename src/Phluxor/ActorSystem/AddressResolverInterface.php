<?php

namespace Phluxor\ActorSystem;

/**
 * AddressResolverInterface is used to resolve remote actors
 *
 */
interface AddressResolverInterface
{
    /**
     * Resolves the address to a Pid
     * @param Pid|null $pid
     * @return ProcessRegistryResult
     */
    public function __invoke(?Pid $pid): ProcessRegistryResult;
}
