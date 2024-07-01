<?php

declare(strict_types=1);

namespace Test\Persistence;

use Phluxor\ActorSystem\ProtoBuf\Terminated;
use Phluxor\ActorSystem\ProtoBuf\TerminatedReason;
use Phluxor\Persistence\Message;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testShouldSerializeMessage(): void
    {
        $t = new Terminated(['why' => TerminatedReason::AddressTerminated]);
        $env = new Message($t);
        $this->assertSame(
            '{"typeName":"Phluxor\\\ActorSystem\\\ProtoBuf\\\Terminated","rawMessage":"{\"why\":\"AddressTerminated\"}"}',
            json_encode($env)
        );
    }
}
