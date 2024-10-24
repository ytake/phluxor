<?php

declare(strict_types=1);

namespace Phluxor\Persistence\PgSql;

use Phluxor\Persistence\RdbmsSchemaInterface;

class DefaultSchema implements RdbmsSchemaInterface
{
    public function __construct(
        private string $journalTable = 'journals',
        private string $snapshotTable = 'snapshots'
    ) {
    }

    public static function newTable(): self
    {
        return new self();
    }

    public function renameJournalTable(string $name): self
    {
        $this->journalTable = $name;
        return $this;
    }

    public function renameSnapshotTable(string $name): self
    {
        $this->snapshotTable = $name;
        return $this;
    }

    public function journalTableName(): string
    {
        return $this->journalTable;
    }

    public function snapshotTableName(): string
    {
        return $this->snapshotTable;
    }

    public function id(): string
    {
        return 'id';
    }

    public function payload(): string
    {
        return 'payload';
    }

    public function actorName(): string
    {
        return 'actor_name';
    }

    public function sequenceNumber(): string
    {
        return 'sequence_number';
    }

    public function created(): string
    {
        return 'created_at';
    }

    /**
     * @return string[]
     */
    public function createTable(): array
    {
        $tables = [
            $this->journalTableName(),
            $this->snapshotTableName(),
        ];
        $createTables = [];
        foreach ($tables as $table) {
            $createTables[] = "CREATE TABLE $table (" .
                $this->id() . "VARCHAR(26) NOT NULL," .
                $this->payload() . "BYTEA NOT NULL," .
                $this->sequenceNumber() . "BIGINT," .
                $this->actorName() . "VARCHAR(255)," .
                $this->created() . "TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP," .
                "PRIMARY KEY (" . $this->id() . ")," .
                "UNIQUE (" . $this->id() . ")," .
                "UNIQUE (" . $this->actorName() . "," . $this->sequenceNumber() . ")" .
                ");";
        }
        return $createTables;
    }
}
