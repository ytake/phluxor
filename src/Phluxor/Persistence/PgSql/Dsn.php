<?php

declare(strict_types=1);

namespace Phluxor\Persistence\PgSql;

readonly class Dsn
{
    /**
     * @param string $host
     * @param int $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $sslMode choice of 'disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public string $password,
        public string $sslMode = 'prefer',
        public array $options = [],
    ) {
    }

    public function __toString(): string
    {
        return "pgsql:host=$this->host;port=$this->port;dbname=$this->database;sslmode=$this->sslMode";
    }
}
