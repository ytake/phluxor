<?php

declare(strict_types=1);

namespace Phluxor;

use Brick\Math\Exception\MathException;
use Phluxor\ActorSystem\Config;
use Phluxor\ActorSystem\DeadLetterProcess;
use Phluxor\ActorSystem\EventStreamProcess;
use Phluxor\ActorSystem\Exception\ExtensionNotLoadedException;
use Phluxor\ActorSystem\GuardiansValue;
use Phluxor\ActorSystem\Metrics;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\ProcessRegistryValue;
use Phluxor\ActorSystem\RootContext;
use Phluxor\ActorSystem\Strategy\SupervisorEvent;
use Phluxor\EventStream\EventStream;
use Phluxor\Value\ContextExtensions;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Swoole\Coroutine\Channel;
use Psr\Log\LoggerInterface;

class ActorSystem
{
    /** @var string */
    public const string LOCAL_ADDRESS = 'nonhost';

    private string $id;
    private bool $stopped = false;

    /** @var ProcessRegistryValue|null */
    private ProcessRegistryValue|null $processRegistry = null;

    /** @var DeadLetterProcess|null */
    private DeadLetterProcess|null $deadLetter = null;

    /** @var GuardiansValue|null */
    private GuardiansValue|null $guardians = null;

    /** @var EventStream|null */
    private EventStream|null $eventStream = null;

    /** @var RootContext|null */
    private RootContext|null $root = null;

    /** @var ContextExtensions|null */
    private ContextExtensions|null $extentions = null;

    /** @var LoggerInterface */
    private LoggerInterface $logger;
    private Channel $stopper;
    private ?Metrics $metrics = null;

    public function __construct(
        private readonly Config $config = new Config(),
    ) {
        if (!extension_loaded('swoole')) {
            throw new ExtensionNotLoadedException(
                "Actor system cannot be executed because swoole extension is not loaded.\n" .
                "Please install the extension first."
            );
        }
        $this->stopper = new Channel(1);
    }

    /**
     * @param Config $config
     * @return ActorSystem
     * @throws MathException
     */
    public static function create(Config $config = new Config()): ActorSystem
    {
        $actor = new ActorSystem($config);
        $actor->id = $actor->generateId();
        $actor->logger = $config->loggerFactory()($actor);
        $actor->processRegistry = new ProcessRegistryValue($actor);
        $actor->root = new RootContext($actor, []);
        $actor->guardians = new GuardiansValue($actor);
        $actor->eventStream = new EventStream();
        $actor->deadLetter = new DeadLetterProcess($actor);
        $actor->extentions = new ContextExtensions();
        $actor->processRegistry->add(new EventStreamProcess($actor), 'eventstream');
        $actor->stopped = false;
        $actor->metrics = new Metrics($actor, $config->metricsProvider());
        $actor->extentions->set($actor->metrics);
        $actor->subscribeSupervision($actor);
        $actor->logger->info('actor system started', ['id' => $actor->id]);
        return $actor;
    }

    /**
     * @throws MathException
     */
    private function generateId(): string
    {
        return (new ShortUuid())->encode(Uuid::uuid4());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function metrics(): ?Metrics
    {
        return $this->metrics;
    }

    /**
     * @param string $id
     * @return Ref
     */
    public function newLocalAddress(string $id): Ref
    {
        return new Ref(new ActorSystem\ProtoBuf\Pid([
            'address' => $this->getProcessRegistry()->getAddress(),
            'id' => $id,
        ]));
    }

    /**
     * @return ProcessRegistryValue
     */
    public function getProcessRegistry(): ProcessRegistryValue
    {
        if ($this->processRegistry === null) {
            throw new RuntimeException('process registry is not initialized');
        }
        return $this->processRegistry;
    }

    /**
     * Retrieves the DeadLetterProcess object associated with this instance.
     *
     * @return DeadLetterProcess
     */
    public function getDeadLetter(): DeadLetterProcess
    {
        if ($this->deadLetter === null) {
            throw new RuntimeException('dead letter is not initialized');
        }
        return $this->deadLetter;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return GuardiansValue
     */
    public function getGuardiansValue(): GuardiansValue
    {
        if ($this->guardians === null) {
            throw new RuntimeException('guardians is not initialized');
        }
        return $this->guardians;
    }

    /**
     * @return EventStream|null
     */
    public function getEventStream(): EventStream|null
    {
        return $this->eventStream;
    }

    public function shutdown(): void
    {
        $close = $this->stopper->close();
        if (!is_bool($close)) {
            throw new RuntimeException('stopper channel close failed');
        }
        $this->stopped = true;
    }

    /**
     * Return the address of the process registry.
     *
     * @return string The address of the process registry.
     *
     * @throws RuntimeException if the process registry is not initialized.
     */
    public function address(): string
    {
        return $this->getProcessRegistry()->getAddress();
    }

    public function isStopped(): bool
    {
        return match ($this->stopped) {
            true => true,
            default => false,
        };
    }

    public function root(): RootContext
    {
        if ($this->root === null) {
            throw new RuntimeException('root context is not initialized');
        }
        return $this->root;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function extensions(): ContextExtensions
    {
        if ($this->extentions === null) {
            throw new RuntimeException('context extensions is not initialized');
        }
        return $this->extentions;
    }

    private function subscribeSupervision(ActorSystem $system): void
    {
        $system->getEventStream()?->subscribe(
            function (mixed $event) use ($system) {
                if ($event instanceof SupervisorEvent) {
                    $system->getLogger()->debug(
                        'supervision',
                        [
                            'actor' => $event->getChild(),
                            'directive' => $event->getDirective(),
                            'reason' => $event->getReason(),
                        ]
                    );
                }
            }
        );
    }
}
