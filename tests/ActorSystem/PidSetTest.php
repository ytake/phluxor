<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Pid;
use Phluxor\ActorSystem\PidSet;
use PHPUnit\Framework\TestCase;

class PidSetTest extends TestCase
{
    public function testPidSetIsEmptyWhenCreated(): void
    {
        $pidSet = new PidSet();
        $this->assertTrue($pidSet->empty());
    }

    public function testPidSetClears(): void
    {
        $pidSet = new PidSet();
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));

        $pid = new \Phluxor\ActorSystem\ProtoBuf\PID();
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));

        $pid = new \Phluxor\ActorSystem\ProtoBuf\PID();
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p3',
        ])));

        $this->assertSame(3, $pidSet->len());
        $pidSet->clear();
        $this->assertTrue($pidSet->empty());
        $this->assertSame(0, $pidSet->len());
    }

    public function testShouldRemovePidFromPidSet(): void
    {
        $pidSet = new PidSet();
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p3',
        ])));
        $this->assertSame(3, $pidSet->len());
        $pidSet->remove(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $this->assertSame(2, $pidSet->len());
    }

    public function testAddSmall(): void
    {
        $pidSet = new PidSet();
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $this->assertFalse($pidSet->empty());
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $this->assertEquals(1, $pidSet->len());
    }

    public function testPidSetValue(): void
    {
        $pidSet = new PidSet();
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $pidSet->add(new Pid(new \Phluxor\ActorSystem\ProtoBuf\PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p3',
        ])));
        $this->assertFalse($pidSet->empty());
        $this->assertCount(3, $pidSet->values());
    }
}
