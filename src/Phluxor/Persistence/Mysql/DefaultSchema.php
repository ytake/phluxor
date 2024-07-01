<?php

declare(strict_types=1);

namespace Phluxor\Persistence\Mysql;

class DefaultSchema implements SchemaInterface
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

    public function createTable(): array
    {
        $tables = [
            $this->journalTableName(),
            $this->snapshotTableName(),
        ];
        $createTables = [];
        foreach ($tables as $table) {
            $createTables[] = "CREATE TABLE `$table` (" .
                "`" . $this->id() . "` varchar(26) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL," .
                "`" . $this->payload() . "` json NOT NULL," .
                "`" . $this->sequenceNumber() . "` bigint DEFAULT NULL," .
                "`" . $this->actorName() . "` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL," .
                "`" . $this->created() . "` timestamp DEFAULT CURRENT_TIMESTAMP," .
                "PRIMARY KEY (`" . $this->id() . "`)," .
                "UNIQUE KEY `uidx_id` (`" . $this->id() . "`)," .
                "UNIQUE KEY `uidx_names` (`" . $this->actorName() . "`,`" . $this->sequenceNumber() . "`)" .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;";
        }
        return $createTables;
    }
}
