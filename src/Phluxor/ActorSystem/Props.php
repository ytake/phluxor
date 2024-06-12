<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use DateInterval;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Context\SpawnerInterface;
use Phluxor\ActorSystem\Dispatcher\CoroutineDispatcher;
use Phluxor\ActorSystem\Dispatcher\DispatcherInterface;
use Phluxor\ActorSystem\Mailbox\MailboxInterface;
use Phluxor\ActorSystem\Mailbox\MailboxProducerInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ProducerInterface;
use Phluxor\ActorSystem\Message\ProducerWithActorSystemInterface;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Message\ContextDecoratorFunctionInterface;
use Phluxor\ActorSystem\Props\OnInitInterface;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;

use function array_merge;

class Props
{
    /** @var ProducerWithActorSystemInterface|Closure(ActorSystem): ActorInterface $producer */
    private ProducerWithActorSystemInterface|Closure $producer;

    /** @var MailboxProducerInterface|null */
    private MailboxProducerInterface|null $mailboxProducer = null;

    /** @var Closure(ContextInterface): ContextInterface|ContextDecoratorFunctionInterface|null */
    private Closure|ContextDecoratorFunctionInterface|null $contextDecoratorFunction = null;

    /** @var SenderMiddlewareInterface[] */
    private array $senderMiddleware = [];

    /** @var Closure(SenderInterface|ContextInterface, Pid, MessageEnvelope): void|SenderFunctionInterface|null */
    private Closure|SenderFunctionInterface|null $senderMiddlewareChain = null;
    private SupervisorStrategyInterface|null $supervisorStrategy = null;
    private SupervisorStrategyInterface|null $guardianStrategy = null;

    /** @var Closure[]|ActorSystem\Props\OnInitInterface[] */
    private array $onInit = [];
    private DispatcherInterface|null $dispatcher = null;

    /** @var Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    private Closure|SpawnFunctionInterface|null $spawner = null;

    /** @var ActorSystem\Props\SpawnMiddlewareInterface[] */
    private array $spawnMiddleware = [];

    /** @var Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null */
    private Closure|SpawnFunctionInterface|null $spawnMiddlewareChain = null;

    /** @var ReceiverMiddlewareInterface[] */
    private array $receiverMiddleware = [];

    /** @var Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface|null */
    private Closure|ReceiverFunctionInterface|null $receiverMiddlewareChain = null;

    /**
     * @param ProducerWithActorSystemInterface|Closure(ActorSystem): ActorInterface $producer
     * @param ActorSystem\Props\ContextDecoratorInterface[] $contextDecorator
     */
    public function __construct(
        ProducerWithActorSystemInterface|Closure $producer,
        private array $contextDecorator = [],
    ) {
        $this->producer = $producer;
    }

    public function getDispatcher(): DispatcherInterface
    {
        if ($this->dispatcher == null) {
            return $this->defaultDispatcher();
        }
        return $this->dispatcher;
    }

    public function withProduceMailbox(MailboxProducerInterface $mailboxProducer): Props
    {
        $this->mailboxProducer = $mailboxProducer;
        return $this;
    }

    public function produceMailbox(): MailboxInterface
    {
        if ($this->mailboxProducer == null) {
            $unbounded = new ActorSystem\Mailbox\Unbounded();
            return $unbounded();
        }
        $mailProducer = $this->mailboxProducer;
        return $mailProducer();
    }

    /**
     * @return Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface
     */
    public function getSpawner(): Closure|SpawnFunctionInterface
    {
        if ($this->spawner == null) {
            return new ActorSystem\Spawner\DefaultSpawner();
        }
        return $this->spawner;
    }

    /**
     * @param ActorSystem $actorSystem
     * @param string $name
     * @param SpawnerInterface $spawner
     * @return SpawnResult
     */
    public function spawn(
        ActorSystem $actorSystem,
        string $name,
        SpawnerInterface $spawner
    ): SpawnResult {
        return $this->getSpawner()($actorSystem, $name, $this, $spawner);
    }

    /**
     * @param ActorSystem $system
     * @return ActorInterface
     */
    public function producer(ActorSystem $system): ActorInterface
    {
        $producer = $this->producer;
        return $producer($system);
    }

    protected function defaultDispatcher(): DispatcherInterface
    {
        return new CoroutineDispatcher(300);
    }

    /**
     * @param ActorContext $ctx
     * @return void
     */
    public function initialize(ActorContext $ctx): void
    {
        if (count($this->onInit) == 0) {
            return;
        }
        foreach ($this->onInit as $init) {
            $init($ctx);
        }
    }

    /**
     * @return Closure(SenderInterface|ContextInterface, Pid, MessageEnvelope): void|SenderFunctionInterface|null
     */
    public function senderMiddlewareChain(): Closure|SenderFunctionInterface|null
    {
        return $this->senderMiddlewareChain;
    }

    /**
     * @return Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null
     */
    public function spawnMiddlewareChain(): Closure|SpawnFunctionInterface|null
    {
        return $this->spawnMiddlewareChain;
    }

    /**
     * @deprecated
     * @return Closure(ContextInterface): ContextInterface|ContextDecoratorFunctionInterface|null
     */
    public function getContextDecoratorChain(): Closure|ContextDecoratorFunctionInterface|null
    {
        if ($this->contextDecoratorFunction == null) {
            return new ActorSystem\Message\DefaultContextDecorator();
        }
        return $this->contextDecoratorFunction;
    }

    /**
     * @return Closure|ContextDecoratorFunctionInterface|null
     */
    public function contextDecoratorChain(): Closure|ContextDecoratorFunctionInterface|null
    {
        return $this->contextDecoratorFunction;
    }

    public function getGuardianStrategy(): SupervisorStrategyInterface|null
    {
        return $this->guardianStrategy;
    }

    public function getSupervisorStrategy(): SupervisorStrategyInterface
    {
        if ($this->supervisorStrategy == null) {
            $this->supervisorStrategy = new ActorSystem\Strategy\OneForOneStrategy(
                10,
                new DateInterval('PT10S'),
                fn($reason) => Directive::Restart
            );
        }
         return $this->supervisorStrategy;
    }

    /**
     * @param Closure(ContextInterface): void|OnInitInterface ...$init
     * @return Closure(Props): void
     */
    public static function withOnInit(Closure|OnInitInterface ...$init): Closure
    {
        return function (Props $props) use ($init) {
            $props->onInit = $init;
        };
    }

    /**
     * @param ProducerInterface $producer
     * @return Closure(Props): void
     */
    public static function withProducer(ProducerInterface $producer): Closure
    {
        return function (Props $props) use ($producer) {
            $props->producer = fn(ActorSystem $system) => $producer();
        };
    }

    /**
     * @param DispatcherInterface|null $dispatcher
     * @return Closure(Props): void
     */
    public static function withDispatcher(DispatcherInterface|null $dispatcher): Closure
    {
        return function (Props $props) use ($dispatcher) {
            $props->dispatcher = $dispatcher;
        };
    }

    /**
     * @param MailboxProducerInterface|null $mailboxProducer
     * @return Closure(Props): void
     */
    public static function withMailboxProducer(MailboxProducerInterface|null $mailboxProducer): Closure
    {
        return function (Props $props) use ($mailboxProducer) {
            $props->mailboxProducer = $mailboxProducer;
        };
    }

    /**
     * @param Props\ContextDecoratorInterface ...$contextDecorator
     * @return Closure(Props): void
     */
    public static function withContextDecorator(
        ActorSystem\Props\ContextDecoratorInterface ...$contextDecorator
    ): Closure {
        return function (Props $props) use ($contextDecorator) {
            $props->contextDecorator = array_merge($props->contextDecorator, $contextDecorator);

            $props->contextDecoratorFunction = makeContextDecoratorChain(
                $props->contextDecorator,
                fn($ctx) => $ctx
            );
        };
    }

    /**
     * @param SupervisorStrategyInterface|null $strategy
     * @return Closure(Props): void
     */
    public static function withGuardian(SupervisorStrategyInterface|null $strategy): Closure
    {
        return function (Props $props) use ($strategy) {
            $props->guardianStrategy = $strategy;
        };
    }

    /**
     * @param SupervisorStrategyInterface|null $strategy
     * @return Closure(Props): void
     */
    public static function withSupervisor(SupervisorStrategyInterface|null $strategy): Closure
    {
        return function (Props $props) use ($strategy) {
            $props->supervisorStrategy = $strategy;
        };
    }

    /**
     * @param ReceiverMiddlewareInterface ...$receiverMiddleware
     * @return Closure(Props): void
     */
    public static function withReceiverMiddleware(
        ReceiverMiddlewareInterface ...$receiverMiddleware
    ): Closure {
        return function (Props $props) use ($receiverMiddleware) {
            $props->receiverMiddleware = array_merge($props->receiverMiddleware, $receiverMiddleware);

            $props->receiverMiddlewareChain = makeReceiverMiddlewareChain(
                $props->receiverMiddleware,
                fn($ctx, $message) => $ctx->receive($message)
            );
        };
    }

    /**
     * @return Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface|null
     */
    public function getReceiverMiddlewareChain(): Closure|ReceiverFunctionInterface|null
    {
        return $this->receiverMiddlewareChain;
    }

    /**
     * @param SenderMiddlewareInterface ...$senderMiddleware
     * @return Closure(Props): void
     */
    public static function withSenderMiddleware(
        ActorSystem\Props\SenderMiddlewareInterface ...$senderMiddleware
    ): Closure {
        return function (Props $props) use ($senderMiddleware) {
            $props->senderMiddleware = array_merge($props->senderMiddleware, $senderMiddleware);

            $props->senderMiddlewareChain = makeSenderMiddlewareChain(
                $props->senderMiddleware,
                function (SenderInterface|ContextInterface $ctx, Pid $pid, MessageEnvelope $envelope) {
                    $pid->sendUserMessage(
                        $ctx->actorSystem(),
                        $envelope->getMessage()
                    );
                }
            );
        };
    }

    /**
     * @param Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null $spawn
     * @return Closure(Props): void
     */
    public static function withSpawnFunc(
        Closure|SpawnFunctionInterface|null $spawn
    ): Closure {
        return function (Props $props) use ($spawn) {
            $props->spawner = $spawn;
        };
    }

    /**
     * @param Message\ReceiveFunction $f
     * @return Closure(Props): void
     */
    public static function withFunc(
        ActorSystem\Message\ReceiveFunction $f
    ): Closure {
        return function (Props $props) use ($f) {
            $props->producer = fn(ActorSystem $system) => $f;
        };
    }

    public static function withSpawnMiddleware(
        ActorSystem\Props\SpawnMiddlewareInterface ...$middleware
    ): Closure {
        return function (Props $props) use ($middleware) {
            $props->spawnMiddleware = array_merge($props->spawnMiddleware, $middleware);
            $props->spawnMiddlewareChain = makeSpawnMiddlewareChain(
                $props->spawnMiddleware,
                function (ActorSystem $actorSystem, string $id, Props $props, SpawnerInterface $context): SpawnResult {
                    if ($props->spawner == null) {
                        $defaultSpawner = new ActorSystem\Spawner\DefaultSpawner();
                        return $defaultSpawner($actorSystem, $id, $props, $context);
                    }
                    $spawner = $props->spawner;
                    return $spawner($actorSystem, $id, $props, $context);
                }
            );
        };
    }

    /**
     * @param Closure(Props): void ...$options
     * @return $this
     */
    public function configure(Closure ...$options): Props
    {
        foreach ($options as $option) {
            $option($this);
        }
        return $this;
    }

    /**
     * creates a props with the given actor producer assigned.
     * @param ProducerInterface|Closure(): ActorInterface $producer
     * @param Closure(Props): void ...$options
     * @return Props
     */
    public static function fromProducer(
        ProducerInterface|Closure $producer,
        Closure ...$options
    ): Props {
        $props = new Props(
            producer: fn(ActorSystem $system) => $producer(),
            contextDecorator: []
        );
        $props->configure(...$options);
        return $props;
    }

    /**
     * creates a props with the given actor producer assigned.
     * @param ProducerWithActorSystemInterface|Closure(ActorSystem): ActorInterface $producer
     * @param Closure(Props): void ...$options
     * @return Props
     */
    public static function fromProducerWithActorSystem(
        ProducerWithActorSystemInterface|Closure $producer,
        Closure ...$options
    ): Props {
        $props = new Props(
            producer: $producer,
            contextDecorator: []
        );
        $props->configure(...$options);
        return $props;
    }

    /**
     * creates a props with the given receive func assigned as the actor producer.
     * @param ActorSystem\Message\ReceiveFunction $f
     * @param Closure(Props): void ...$options
     * @return Props
     */
    public static function fromFunction(
        ActorSystem\Message\ReceiveFunction $f,
        Closure ...$options
    ): Props {
        return static::fromProducer(fn(): ActorInterface => $f, ...$options);
    }

    /**
     * @param Closure(Props): void ...$options
     * @return Props
     */
    public function clone(Closure ...$options): Props
    {
        $props = static::fromProducerWithActorSystem(
            $this->producer,
            static::withDispatcher($this->dispatcher),
            static::withMailboxProducer($this->mailboxProducer),
            static::withContextDecorator(...$this->contextDecorator),
            static::withGuardian($this->guardianStrategy),
            static::withSupervisor($this->supervisorStrategy),
            static::withReceiverMiddleware(...$this->receiverMiddleware),
            static::withSenderMiddleware(...$this->senderMiddleware),
            static::withSpawnFunc($this->spawner),
            static::withSpawnMiddleware(...$this->spawnMiddleware),
            static::withOnInit(...$this->onInit),
        );
        $props->configure(...$options);
        return $props;
    }
}
