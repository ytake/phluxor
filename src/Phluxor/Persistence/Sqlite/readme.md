# Phluxor SQLite Persistence Adapter

persisting Phluxor actor state to a SQLite database.  

This package provides a SQLite persistence layer for Phluxor.

## Usage

use `Phluxor\Persistence\Mixin` trait and implement `Phluxor\Persistence\PersistentInterface`.

```php
<?php

declare(strict_types=1);

namespace Example\Persistence;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\Persistence\Message\RequestSnapshot;
use Phluxor\Persistence\Mixin;
use Test\Persistence\ProtoBuf\TestMessage;
use Test\Persistence\ProtoBuf\TestSnapshot;
use Test\Persistence\Query;

class PersistenceActor implements ActorInterface
{
    use Mixin;

    private string $state = '';

    public function receive(ContextInterface $context): void
    {
        $msg = $context->message();
        switch (true) {
            case $msg instanceof RequestSnapshot:
                $this->persistenceSnapshot(new TestSnapshot(['message' => $this->state]));
                break;
            case $msg instanceof TestSnapshot:
                $this->state = $msg->getMessage();
                break;
            case $msg instanceof TestMessage:
                if (!$this->recovering()) {
                    $this->persistenceReceive($msg);
                }
                $this->state = $msg->getMessage();
                break;
            case $msg instanceof Query:
                $context->respond($this->state);
                break;
        }
    }
}
```

use `Phluxor\Persistence\Sqlite\SqliteProvider`.

```php

<?php

declare(strict_types=1);

namespace Example\Persistence;

use Phluxor\ActorSystem;
use Phluxor\Persistence\EventSourcedBehavior;
use Phluxor\Persistence\Sqlite\Connection;
use Phluxor\Persistence\Sqlite\DefaultSchema;
use Phluxor\Persistence\Sqlite\SqliteProvider;
use Psr\Log\LoggerInterface;
use Test\Persistence\ProtoBuf\TestMessage;

use function Swoole\Coroutine\run;

class SampleSystem
{
    public function main(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
                $system = ActorSystem::create();
                $props = ActorSystem\Props::fromProducer(fn() => new PersistenceActor(),
                    ActorSystem\Props::withReceiverMiddleware(
                        new EventSourcedBehavior(
                            $this->sqliteProvider($system->getLogger(), 3)
                        )
                    ));
                $ref = $system->root()->spawnNamed($props, 'test.actor');
                $system->root()->send($ref->getRef(), new TestMessage(['message' => 'hello']));
            });
        });
    }

    private function sqliteProvider(
        LoggerInterface $logger,
        int $snapshotInterval
    ): SqliteProvider {
        $conn = new Connection($this->sqlitePath());
        return new SqliteProvider(
            $conn->proxy(),
            new DefaultSchema(),
            $snapshotInterval,
            $logger
        );
    }
}
```

# Default table schema

use ULID as id(varchar(26)) and json as payload.  

see [Default Schema](DefaultSchema.php)

```sql
CREATE TABLE journals
(
    id              TEXT NOT NULL PRIMARY KEY,
    payload         BLOB NOT NULL,
    sequence_number INTEGER NOT NULL,
    actor_name      TEXT NOT NULL,
    created_at      TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    UNIQUE (id),
    UNIQUE (actor_name, sequence_number)
);

CREATE TABLE snapshots
(
    id              TEXT NOT NULL PRIMARY KEY,
    payload         BLOB NOT NULL,
    sequence_number INTEGER NOT NULL,
    actor_name      TEXT NOT NULL,
    created_at      TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    UNIQUE (id),
    UNIQUE (actor_name, sequence_number)
);
```

## change table name

for journal table and snapshot table, you can change table name by implementing `Phluxor\Persistence\RdbmsSchemaInterface`.  

```php
<?php

declare(strict_types=1);

namespace Phluxor\Persistence;

interface RdbmsSchemaInterface
{
    public function journalTableName(): string;

    public function snapshotTableName(): string;

    public function id(): string;

    public function payload(): string;

    public function actorName(): string;

    public function sequenceNumber(): string;

    public function created(): string;

    public function createTable(): array;
}
```
