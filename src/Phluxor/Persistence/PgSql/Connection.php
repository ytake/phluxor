<?php

declare(strict_types=1);

namespace Phluxor\Persistence\PgSql;

use Phluxor\Persistence\Exception\ConnectionFailedException;
use Swoole\Database\PDOProxy;

readonly class Connection
{
    public function __construct(
        private Dsn $dsn,
    ) {
    }

    public function proxy(): PDOProxy
    {
        $pool = new PgSqlPool(
            (string)$this->dsn,
            $this->dsn->username,
            $this->dsn->password,
        );
        $result = $pool->get();
        if (!$result) {
            throw new ConnectionFailedException(
                'connection filed.'
            );
        }
        return $result;
    }
}
