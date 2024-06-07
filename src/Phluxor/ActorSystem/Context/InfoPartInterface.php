<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Pid;
use Psr\Log\LoggerInterface;

interface InfoPartInterface
{
    public function parent(): Pid|null;

    public function self(): Pid|null;

    public function actor(): ActorInterface;

    public function actorSystem(): ActorSystem;

    public function logger(): LoggerInterface;
}
