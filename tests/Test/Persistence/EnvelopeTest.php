<?php

declare(strict_types=1);

namespace Test\Persistence;

use Phluxor\ActorSystem\ProtoBuf\Terminated;
use Phluxor\ActorSystem\ProtoBuf\TerminatedReason;
use Phluxor\Persistence\Envelope;
use Phluxor\Persistence\Message;
use PHPUnit\Framework\TestCase;

class EnvelopeTest extends TestCase
{
    public function testShouldSerializeMessage(): void
    {
        $message = new Message(
            new Terminated(
                ['why' => TerminatedReason::AddressTerminated]
            )
        );
        $env = new Envelope('id', json_encode($message), 1, 'actorName', 'eventName');
        $o = $env->message();
        $this->assertInstanceOf(Terminated::class, $o);
        $this->assertSame(1, $o->getWhy());
    }
}
