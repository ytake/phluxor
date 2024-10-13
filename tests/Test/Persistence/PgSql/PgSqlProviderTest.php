<?php

namespace Test\Persistence\PgSql;

use Google\Protobuf\Internal\Message;
use Phluxor\ActorSystem;
use Phluxor\Persistence\PgSql\DefaultRdbmsSchema;
use Phluxor\Persistence\PgSql\Dsn;
use Phluxor\Persistence\PgSql\PgSqlProvider;
use PHPUnit\Framework\TestCase;

use Test\Persistence\ProtoBuf\UserCreated;

use function Swoole\Coroutine\run;

class PgSqlProviderTest extends TestCase
{
    private Dsn $dsn;

    protected function setUp(): void
    {
        $this->dsn = new Dsn(
            '127.0.0.1',
            5432,
            'sample',
            'postgres',
            'postgres'
        );
    }

    public function tearDown(): void
    {
        run(function () {
            go(function () {
                $conn = new \PDO((string)$this->dsn, $this->dsn->username, $this->dsn->password);
                $conn->exec('TRUNCATE journals;');
                $conn->exec('TRUNCATE snapshots;');
                $conn = null;
            });
        });
    }

    public function testPersistEvent(): void
    {
        run(function () {
            go(function () {
                $provider = $this->pgsqlProvider();
                $event = new UserCreated([
                    'userID' => 'test',
                    'userName' => 'test',
                    'email' => '',
                ]);
                $provider->persistenceEvent('user', 1, $event);
                $processed = false;
                $provider->getEvents('user', 1, 4, function (Message $e) use (&$processed) {
                    $this->assertInstanceOf(UserCreated::class, $e);
                    $this->assertSame('test', $e->getUserName());
                    $this->assertSame('test', $e->getUserID());
                    $this->assertSame('', $e->getEmail());
                    $processed = true;
                });
                $this->assertTrue($processed);
                $processed = false;
                $provider->getEvents('user', 1, 0, function (Message $e) use (&$processed) {
                    $this->assertInstanceOf(UserCreated::class, $e);
                    $this->assertSame('test', $e->getUserName());
                    $this->assertSame('test', $e->getUserID());
                    $this->assertSame('', $e->getEmail());
                    $processed = true;
                });
                $this->assertTrue($processed);
            });
        });
    }

    public function testPersistSnapshot(): void
    {
        run(function () {
            go(function () {
                $provider = $this->pgsqlProvider();
                $event = new UserCreated([
                    'userID' => 'test',
                    'userName' => 'test',
                    'email' => '',
                ]);
                $provider->persistenceSnapshot('user', 1, $event);
                $result = $provider->getSnapshot('user');
                $this->assertInstanceOf(UserCreated::class, $result->getSnapshot());
                $this->assertSame('test', $result->getSnapshot()->getUserName());
                $this->assertSame('test', $result->getSnapshot()->getUserID());
                $this->assertSame('', $result->getSnapshot()->getEmail());

                $result = $provider->getSnapshot('1');
                $this->assertNull($result->getSnapshot());
                $processed = false;
                $provider->getEvents('user', 1, 0, function (Message $e) use (&$processed) {
                    $processed = true;
                });
                $this->assertFalse($processed);
            });
        });
    }

    private function pgsqlProvider(): PgSqlProvider
    {
        $conn = new \Phluxor\Persistence\PgSql\Connection(
            $this->dsn
        );
        return new PgSqlProvider(
            $conn->proxy(),
            new DefaultRdbmsSchema(),
            3,
            ActorSystem::create()->getLogger()
        );
    }
}
