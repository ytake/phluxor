<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Ref;
use Psr\Log\LoggerInterface;

interface InfoPartInterface
{
    public function parent(): Ref|null;

    public function self(): Ref|null;

    public function actor(): ActorInterface;

    public function actorSystem(): ActorSystem;

    public function logger(): LoggerInterface;
}
