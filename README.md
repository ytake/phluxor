# Phluxor

A toolkit for flexible actor models in PHP, empowering the PHP ecosystem.

requires PHP 8.3 and swoole 5.0 or later.

and Protocol Buffers for message serialization. / not supported other serialization formats yet.

Documentation is under preparation.

do not use this in production yet.

## work in progress

- persistent actors (soon)
- router / round-robin, broadcast, scatter-gather, etc.
- open telemetry support (tracing, metrics, etc.)
- virtual actors / cluster support
- typed streams

already implemented:

- actor model
- actor lifecycle
- supervision
- actor registry
- actor messaging
- become/unbecome
- mailbox / dispatcher
- event stream
- future

now local actors are supported, and remote actors are in progress.

## Supervision

exception handling is done by the actor system, and the actor can be supervised by parent , root actors.

- `OneForOneStrategy`
- `AllForOneStrategy`
- `ExponentialBackoffStrategy`
- `RestartStrategy`

## Become/Unbecome

an actor can change its behavior by `become` and `unbecome` methods.

example:

one, other are the behavior methods.

```php
<?php

declare(strict_types=1);

namespace Acme;

use Phluxor\ActorSystem\Behavior;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\ReceiveFunction;

class EchoSetBehaviorActor implements ActorInterface
{
    private Behavior $behavior;

    public function __construct()
    {
        $this->behavior = new Behavior();
        $this->behavior->become(
            new ReceiveFunction(
                fn(ContextInterface $context) => $this->one($context)
            )
        );
    }

    public function receive(ContextInterface $context): void
    {
        $this->behavior->receive($context);
    }

    public function one(ContextInterface $context): void
    {
        if ($context->message() instanceof BehaviorMessage) {
            $this->behavior->become(
                new ReceiveFunction(
                    fn(ContextInterface $context) => $this->other($context)
                )
            );
        }
    }

    public function other(ContextInterface $context): void
    {
        if ($context->message() instanceof EchoRequest) {
            $context->respond(new EchoResponse());
        }
    }
}
```

```php
<?php

declare(strict_types=1);

use Acme\BehaviorMessage;
use Acme\EchoSetBehaviorActor;
use Acme\EchoRequest;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\ActorContext;
use Phluxor\ActorSystem\Props;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

function main(): void 
{
    run(function () {
        $system = ActorSystem::create();
        go(function (ActorSystem $system) {
            $pid = $system->root()->spawn(
                Props::fromProducer(
                    fn() => new EchoSetBehaviorActor()
                )
            );
            $system->root()->send($pid, new BehaviorMessage());
            $future = $system->root()->requestFuture($pid, new EchoRequest(), 1);
            var_dump($future->result());
            $system->root()->stop($pid);
        }, $system);
    });
}

```
