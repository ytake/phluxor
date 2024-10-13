<?php

declare(strict_types=1);

namespace Test\Persistence\PgSql;

use PDO;
use Phluxor\Persistence\PgSql\Connection;
use Phluxor\Persistence\PgSql\Dsn;
use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\run;

class PgSqlConnectionTest extends TestCase
{
    public function testConnection(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
                $pool = new Connection(
                    new Dsn(
                        '127.0.0.1',
                        5432,
                        'sample',
                        'postgres',
                        'postgres'
                    )
                );
                /** @var PDO $conn */
                $conn = $pool->proxy();
                $st = $conn->query("SELECT NOW()");
                $this->assertNotFalse($st->fetchAll());
            });
        });
    }
}