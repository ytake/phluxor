<?php

declare(strict_types=1);

namespace Phluxor\Persistence\PgSql;

use Closure;
use Google\Protobuf\Internal\Message;
use PDO;
use PDOException;
use Phluxor\Persistence\Envelope;
use Phluxor\Persistence\ProviderInterface;
use Phluxor\Persistence\ProviderStateInterface;
use Phluxor\Persistence\RdbmsSchemaInterface;
use Phluxor\Persistence\SnapshotResult;
use Psr\Log\LoggerInterface;
use ReflectionException;
use Swoole\Database\PDOProxy;
use Symfony\Component\Uid\Ulid;

use function json_encode;

/**
 * persistence provider for mysql
 */
readonly class PgSqlProvider implements ProviderStateInterface, ProviderInterface
{
    public function __construct(
        private PDOProxy $connection,
        private RdbmsSchemaInterface $schema,
        private int $snapshotInterval,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return string
     */
    private function selectColumns(): string
    {
        return implode(
            ',',
            [
                $this->schema->id(),
                $this->schema->payload(),
                $this->schema->sequenceNumber(),
                $this->schema->actorName(),
            ]
        );
    }

    /**
     * @param Closure(PDOProxy): bool $callback
     * @return void
     */
    private function executeTx(Closure $callback): void
    {
        $conn = $this->connection;
        $conn->reset();
        $conn->beginTransaction();
        $result = $callback($conn);
        $result === false ? $conn->rollBack() : $conn->commit();
    }

    /**
     * @param string $actorName
     * @param int $eventIndexStart
     * @param int $eventIndexEnd
     * @param Closure(Message): void $callback
     * @return void
     * @throws ReflectionException
     */
    public function getEvents(string $actorName, int $eventIndexStart, int $eventIndexEnd, Closure $callback): void
    {
        /** @var PDO $conn */
        $conn = $this->connection;
        $conn->beginTransaction();
        $query = sprintf(
            'SELECT %s FROM %s WHERE %s = ? AND %s BETWEEN ? AND ? ORDER BY %s ASC',
            $this->selectColumns(),
            $this->schema->journalTableName(),
            $this->schema->actorName(),
            $this->schema->sequenceNumber(),
            $this->schema->sequenceNumber(),
        );
        $args = [$actorName, $eventIndexStart, $eventIndexEnd];
        if ($eventIndexEnd === 0) {
            $query = sprintf(
                'SELECT %s FROM %s WHERE %s = ? AND %s >= ? ORDER BY %s ASC',
                $this->selectColumns(),
                $this->schema->journalTableName(),
                $this->schema->actorName(),
                $this->schema->sequenceNumber(),
                $this->schema->sequenceNumber(),
            );
            $args = [$actorName, $eventIndexStart];
        }
        $stmt = $conn->prepare($query);
        $stmt->execute($args);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conn->commit();
        foreach ($rows as $row) {
            $env = new Envelope(stream_get_contents($row['payload']));
            $callback($env->message());
        }
    }

    /**
     * @param string $actorName
     * @param int $eventIndex
     * @param Message $event
     * @return void
     */
    public function persistenceEvent(string $actorName, int $eventIndex, Message $event): void
    {
        $msg = new \Phluxor\Persistence\Message($event);
        $this->executeTx(function (PDOProxy $conn) use ($msg, $eventIndex, $actorName) {
            try {
                /** @var \PDO $conn */
                $stmt = $conn->prepare(
                    sprintf(
                        'INSERT INTO %s (%s) VALUES (?, ?, ?, ?)',
                        $this->schema->journalTableName(),
                        $this->selectColumns()
                    )
                );
                $ulid = (string)(new Ulid());
                $encoded = json_encode($msg);
                $stmt->bindParam(1, $ulid);
                $stmt->bindParam(2, $encoded, \PDO::PARAM_LOB);
                $stmt->bindParam(3, $eventIndex);
                $stmt->bindParam(4, $actorName);
                $result = $stmt->execute();
                if ($result === false) {
                    $this->logger->error('Failed to insert event', ['actor' => $actorName]);
                }
                return $result;
            } catch (PDOException $e) {
                $this->logger->error('error on persistenceEvent', ['actor' => $actorName, 'error' => $e->getMessage()]);
                return false;
            }
        });
    }

    public function restart(): void
    {
        $this->connection->reconnect();
    }

    public function getSnapshotInterval(): int
    {
        return $this->snapshotInterval;
    }

    /**
     * @param string $actorName
     * @return SnapshotResult
     * @throws ReflectionException
     */
    public function getSnapshot(string $actorName): SnapshotResult
    {
        /** @var PDO $conn */
        $conn = $this->connection;
        $conn->beginTransaction();
        $stmt = $conn->prepare(
            sprintf(
                'SELECT %s FROM %s WHERE %s = ? ORDER BY %s DESC LIMIT 1',
                $this->selectColumns(),
                $this->schema->snapshotTableName(),
                $this->schema->actorName(),
                $this->schema->sequenceNumber(),
            )
        );
        $stmt->execute([$actorName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn->commit();
        if ($row === false) {
            return new SnapshotResult(null, 0, false);
        }
        $env = new Envelope(stream_get_contents($row['payload']));
        return new SnapshotResult($env->message(), $row['sequence_number'], true);
    }

    /**
     * @param string $actorName
     * @param int $snapshotIndex
     * @param Message $snapshot
     * @return void
     */
    public function persistenceSnapshot(string $actorName, int $snapshotIndex, Message $snapshot): void
    {
        $msg = new \Phluxor\Persistence\Message($snapshot);
        $this->executeTx(function (PDOProxy $conn) use ($msg, $snapshotIndex, $actorName) {
            try {
                $stmt = $conn->prepare(
                    sprintf(
                        'INSERT INTO %s (%s) VALUES (?, ?, ?, ?)',
                        $this->schema->snapshotTableName(),
                        $this->selectColumns()
                    )
                );
                $ulid = (string)(new Ulid());
                $encoded = json_encode($msg);
                $stmt->bindParam(1, $ulid);
                $stmt->bindParam(2, $encoded, PDO::PARAM_LOB);
                $stmt->bindParam(3, $snapshotIndex);
                $stmt->bindParam(4, $actorName);
                $result = $stmt->execute();
                if ($result === false) {
                    $this->logger->error('Failed to insert snapshot', ['actor' => $actorName]);
                }
                return $result;
            } catch (PDOException $e) {
                $this->logger->error(
                    'error on persistenceSnapshot',
                    ['actor' => $actorName, 'error' => $e->getMessage()]
                );
                return false;
            }
        });
    }

    public function getState(): ProviderStateInterface
    {
        return $this;
    }
}
