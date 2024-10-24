<?php

declare(strict_types=1);

namespace Phluxor\Persistence\Sqlite;

use Phluxor\Persistence\Exception\ConnectionFailedException;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOProxy;

readonly class Connection
{
    public function __construct(
        private string $dbPath,
    ) {
    }

    public function proxy(): PDOProxy
    {
        $pool = new PDOPool(
            (new PDOConfig())
                ->withDriver('sqlite')
                ->withDbname($this->dbPath)
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
