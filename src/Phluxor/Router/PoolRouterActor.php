<?php

declare(strict_types=1);

namespace Phluxor\Router;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\Started;
use Phluxor\ActorSystem\Props;
use Phluxor\ActorSystem\ProtoBuf\PID;
use Phluxor\ActorSystem\ProtoBuf\PoisonPill;
use Phluxor\ActorSystem\ProtoBuf\Terminated;
use Phluxor\ActorSystem\Ref;
use Phluxor\Router\Message\Broadcast;
use Phluxor\Router\ProtoBuf\AddRoutee;
use Phluxor\Router\ProtoBuf\GetRoutees;
use Phluxor\Router\ProtoBuf\RemoveRoutee;
use Phluxor\Router\ProtoBuf\Routees;
use Swoole\Coroutine\WaitGroup;

readonly class PoolRouterActor implements ActorInterface
{
    /**
     * @param Props $props
     * @param ConfigInterface $config
     * @param StateInterface $state
     * @param WaitGroup $wg
     */
    public function __construct(
        private Props $props,
        private ConfigInterface $config,
        private StateInterface $state,
        private WaitGroup $wg
    ) {
    }

    public function receive(ContextInterface $context): void
    {
        $msg = $context->message();
        switch (true) {
            case $msg instanceof Started:
                $this->config->onStarter($context, $this->props, $this->state);
                $this->wg->done();
                break;
            case $msg instanceof AddRoutee:
                $r = $this->state->getRoutees();
                $ref = new Ref($msg->getPID());
                if ($r->contains($ref)) {
                    break;
                }
                $context->watch($ref);
                $r->add($ref);
                $this->state->registerRoutees($r);
                break;
            case $msg instanceof RemoveRoutee:
                $r = $this->state->getRoutees();
                $ref = new Ref($msg->getPID());
                if (!$r->contains($ref)) {
                    break;
                }
                $context->unwatch($ref);
                $r->remove($ref);
                $this->state->registerRoutees($r);
                usleep(1000 * 1000);
                $context->send($ref, new PoisonPill());
                break;
            case $msg instanceof Broadcast:
                $r = $this->state->getRoutees();
                $sender = $context->sender();
                $r->forEach(
                    fn(int $int, Ref $ref) =>
                    $context->requestWithCustomSender(
                        $ref,
                        $msg->getMessage(),
                        $sender
                    )
                );
                break;
            case $msg instanceof GetRoutees:
                $r = $this->state->getRoutees();
                /** @var PID[] $routees */
                $routees = [];
                $r->forEach(function (int $int, Ref $pid) use (&$routees) {
                    $routees[] = $pid->protobufPid();
                });
                $context->respond(new Routees(['PIDs' => $routees]));
                break;
            case $msg instanceof Terminated:
                $r = $this->state->getRoutees();
                if ($r->remove($msg->getWho())) {
                    $this->state->registerRoutees($r);
                }
                break;
        }
    }
}
