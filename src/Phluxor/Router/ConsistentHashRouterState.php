<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\RefSet;
use Phluxor\Router\ConsistentHash\ConsistentHashException;
use Phluxor\Router\ConsistentHash\HasherInterface;
use Phluxor\Router\ConsistentHash\HashmapContainer;
use Phluxor\Router\ConsistentHash\HashRing;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class ConsistentHashRouterState implements StateInterface
{
    private ?HashmapContainer $hmc = null;

    public function __construct(
        private ?SenderInterface $sender = null
    ) {
    }

    public function routeMessage(mixed $message): void
    {
        $unwrap = MessageEnvelope::unwrapEnvelope($message);
        $msg = $unwrap['message'];
        switch (true) {
            case $msg instanceof HasherInterface:
                $hash = $msg->hash();
                try {
                    $node = $this->hmc->Hashring()->getServer($hash);
                } catch (ConsistentHashException $e) {
                    return;
                }
                $ref = $this->hmc->getRouteeMap()[$node];
                $this->sender->send($ref, $msg);
            default:
                //
        }
    }

    public function registerRoutees(RefSet $routees): void
    {
        $hmc = new HashmapContainer();
        $nodes = [];
        $routees->forEach(
            function (int $int, Ref $ref) use (&$nodes, &$hmc) {
                $nodeName = sprintf('%s@%s', $ref->protobufPid()->getAddress(), $ref);
                $nodes[$int] = $nodeName;
                $hmc->addRoutee($nodeName, $ref);
            }
        );
        $hashring = new HashRing(new Psr16Cache(new ArrayAdapter()));
        $hmc->setHashring($hashring->createContinuum($nodes));
        $this->hmc = $hmc;
    }

    public function getRoutees(): RefSet
    {
        $routees = new RefSet();
        foreach ($this->hmc->getRouteeMap() as $v) {
            $routees->add($v);
        }
        return $routees;
    }

    public function setSender(ContextInterface|SenderInterface $sender): void
    {
        $this->sender = $sender;
    }
}
