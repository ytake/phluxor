<?php

declare(strict_types=1);

namespace Phluxor\Persistence\MySql;

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOProxy;

readonly class Connection
{
    public function __construct(
        private Dsn $dsn,
    ) {
    }

    public function proxy(): PDOProxy
    {
        $pool = new PDOPool(
            (new PDOConfig())
                ->withHost($this->dsn->host)
                ->withPort($this->dsn->port)
                ->withDbName($this->dsn->database)
                ->withCharset($this->dsn->charset)
                ->withUsername($this->dsn->username)
                ->withPassword($this->dsn->password)
        );
        return $pool->get();
    }
}
