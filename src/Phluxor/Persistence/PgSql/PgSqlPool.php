<?php

declare(strict_types=1);

namespace Phluxor\Persistence\PgSql;

use Swoole\ConnectionPool;
use Swoole\Database\PDOProxy;

class PgSqlPool extends ConnectionPool
{
    public function __construct(
        public string $dsn,
        public string $username,
        public string $password,
        public array $options = [],
        int $size = self::DEFAULT_SIZE,
    ) {
        parent::__construct(function () {
            return new \PDO(
                $this->dsn,
                $this->username,
                $this->password,
                $this->options,
            );
        }, $size, PDOProxy::class);
    }
}
