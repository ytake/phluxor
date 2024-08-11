<?php

declare(strict_types=1);

namespace Phluxor\Persistence\Mysql;

use OpenSwoole\Core\Coroutine\Client\PDOClient;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOProxy;

readonly class Connection
{
    public function __construct(
        private Dsn $dsn,
    ) {
    }

    /**
     * @phpstan-ignore-next-line
     * @return PDOProxy|PDOClient
     */
    public function proxy(): PDOProxy|PDOClient
    {
        if (extension_loaded('swoole')) {
            $pool = new PDOPool(  // @phpstan-ignore-line
                (new PDOConfig()) // @phpstan-ignore-line
                ->withHost($this->dsn->host)
                    ->withPort($this->dsn->port)
                    ->withDbName($this->dsn->database)
                    ->withCharset($this->dsn->charset)
                    ->withUsername($this->dsn->username)
                    ->withPassword($this->dsn->password)
            );
            return $pool->get(); // @phpstan-ignore-line
        }
        return new \OpenSwoole\Core\Coroutine\Client\PDOClient(  // @phpstan-ignore-line
            (new \OpenSwoole\Core\Coroutine\Client\PDOConfig())  // @phpstan-ignore-line
                ->withHost($this->dsn->host)
                ->withPort($this->dsn->port)
                ->withDbName($this->dsn->database)
                ->withCharset($this->dsn->charset)
                ->withUsername($this->dsn->username)
                ->withPassword($this->dsn->password)
        );
    }
}
