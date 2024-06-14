<?php

namespace Phluxor\ActorSystem;

/**
 * AddressResolverInterface is used to resolve remote actors
 *
 */
interface AddressResolverInterface
{
    /**
     * Resolves the address to a Ref
     * @param Ref|null $pid
     * @return ProcessRegistryResult
     */
    public function __invoke(?Ref $pid): ProcessRegistryResult;
}
