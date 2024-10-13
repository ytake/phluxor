<?php

declare(strict_types=1);

namespace Test\Persistence\MySql;

use Google\Protobuf\Internal\Message;
use PDO;
use Phluxor\ActorSystem;
use Phluxor\Persistence\MySql\Connection;
use Phluxor\Persistence\MySql\DefaultSchema;
use Phluxor\Persistence\MySql\Dsn;
use Phluxor\Persistence\MySql\MysqlProvider;
use PHPUnit\Framework\TestCase;
use Test\Persistence\ProtoBuf\UserCreated;

use function Swoole\Coroutine\run;

class MysqlProviderTest extends TestCase
{
    public function tearDown(): void
    {
        run(function () {
            go(function () {
                $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sample;charset=utf8mb4', 'user', 'passw@rd');
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
                $provider = $this->mysqlProvider();
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
                $provider = $this->mysqlProvider();
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
                    // should not be called
                    // journal is empty
                    $processed = true;
                });
                $this->assertFalse($processed);
            });
        });
    }

    private function mysqlProvider(): MysqlProvider
    {
        $conn = new Connection(
            new Dsn(
                '127.0.0.1',
                3306,
                'sample',
                'user',
                'passw@rd'
            ));
        return new MysqlProvider(
            $conn->proxy(),
            new DefaultSchema(),
            3,
            ActorSystem::create()->getLogger()
        );
    }
}
