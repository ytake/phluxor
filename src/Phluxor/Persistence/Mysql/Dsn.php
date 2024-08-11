<?php

declare(strict_types=1);

namespace Phluxor\Persistence\Mysql;

readonly class Dsn
{
    public function __construct(
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public string $password,
        public string $charset = 'utf8mb4',
    ) {
    }

    public function __toString(): string
    {
        return "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
    }
}
