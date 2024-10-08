<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ProtoBuf\Pid;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use PHPUnit\Framework\TestCase;

class RefSetTest extends TestCase
{
    public function testPidSetIsEmptyWhenCreated(): void
    {
        $pidSet = new RefSet();
        $this->assertTrue($pidSet->empty());
    }

    public function testPidSetClears(): void
    {
        $pidSet = new RefSet();
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $pidSet->add(new Ref(new Pid([
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
        $pidSet = new RefSet();
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p3',
        ])));
        $this->assertSame(3, $pidSet->len());
        $pidSet->remove(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $this->assertSame(2, $pidSet->len());
    }

    public function testAddSmall(): void
    {
        $pidSet = new RefSet();
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $this->assertFalse($pidSet->empty());
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $this->assertEquals(1, $pidSet->len());
    }

    public function testPidSetValue(): void
    {
        $pidSet = new RefSet();
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p1',
        ])));
        $pidSet->add(new Ref(new PID([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p2',
        ])));
        $pidSet->add(new Ref(new Pid([
            'address' => ActorSystem::LOCAL_ADDRESS,
            'id' => 'p3',
        ])));
        $this->assertFalse($pidSet->empty());
        $this->assertCount(3, $pidSet->values());
    }
}
