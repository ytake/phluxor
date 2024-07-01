<?php

declare(strict_types=1);

namespace Test\EventStream;

use Phluxor\EventStream\EventStream;
use PHPUnit\Framework\TestCase;

class EventStreamTest extends TestCase
{
    public function testSubscribe(): void
    {
        $es = new EventStream();
        $sub = $es->subscribe(function (mixed $event) {
        });
        $this->assertNotNull($sub);
        $this->assertEquals(1, $es->length());
    }

    public function testUnsubscribe(): void
    {
        $es = new EventStream();
        $c1 = 0;
        $c2 = 0;
        $sub1 = $es->subscribe(function (mixed $_) use (&$c1) {
            $c1++;
        });
        $sub2 = $es->subscribe(function (mixed $_) use (&$c2) {
            $c2++;
        });
        $this->assertEquals(2, $es->length());
        $es->unsubscribe($sub2);
        $this->assertEquals(1, $es->length());

        $es->publish(1);
        $this->assertEquals(1, $c1);

        $es->unsubscribe($sub1);
        $this->assertEquals(0, $es->length());

        $es->publish(1);
        $this->assertEquals(1, $c1);
        $this->assertEquals(0, $c2);
    }

    public function testPublish(): void
    {
        $es = new EventStream();
        $v = 0;
        $es->subscribe(function (mixed $m) use (&$v) {
            $v = $m;
        });
        $es->publish(1);
        $this->assertEquals(1, $v);
        $es->publish(100);
        $this->assertEquals(100, $v);
    }

    public function testSubscribeWithPredicateIsCalled(): void
    {
        $es = new EventStream();
        $called = false;
        $es->subscribeWithPredicate(
            function (mixed $_) use (&$called) {
                $called = true;
            },
            function (mixed $m): bool {
                return true;
            }
        );
        $es->publish("");
        $this->assertTrue($called);
    }

    public function testSubscribeWithPredicateIsNotCalled(): void
    {
        $es = new EventStream();
        $called = false;
        $es->subscribeWithPredicate(
            function (mixed $_) use (&$called) {
                $called = true;
            },
            function (mixed $m): bool {
                return false;
            }
        );
        $es->publish("");
        $this->assertFalse($called);
    }

    public function testBenchmarkEventStream(): void
    {
        $es = new EventStream();
        $subs = [];
        for ($i = 0; $i < 1000; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $sub = $es->subscribe(function (mixed $evt) use ($i) {
                    $this->assertEquals($i, $evt);
                });
                $subs[$j] = $sub;
            }

            $es->publish($i);
            foreach ($subs as $sub) {
                $es->unsubscribe($sub);
                $this->assertFalse($sub->isActive());
            }
        }
    }
}
