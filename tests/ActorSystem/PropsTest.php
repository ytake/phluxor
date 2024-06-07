<?php

declare(strict_types=1);

namespace Test\ActorSystem;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ReceiveFunction;
use Phluxor\ActorSystem\Props;
use PHPUnit\Framework\TestCase;

class PropsTest extends TestCase
{
    public function testPropFromProducer(): void
    {
        $fun = Props::fromFunction(
            new ReceiveFunction(function (ContextInterface $context) {
            }),
            Props::withOnInit(function (ContextInterface $context) {
            })
        );
        $this->assertNotSame($fun, $fun->clone());
    }
}
