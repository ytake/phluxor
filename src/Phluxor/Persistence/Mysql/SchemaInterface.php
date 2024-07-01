<?php

declare(strict_types=1);

namespace Phluxor\Persistence\Mysql;

interface SchemaInterface
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
