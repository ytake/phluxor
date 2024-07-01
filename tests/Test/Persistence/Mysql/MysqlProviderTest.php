<?php

declare(strict_types=1);

namespace Test\Persistence\Mysql;

use Google\Protobuf\Internal\Message;
use Phluxor\ActorSystem;
use Phluxor\Persistence\Mysql\DefaultSchema;
use Phluxor\Persistence\Mysql\Dsn;
use Phluxor\Persistence\Mysql\MysqlProvider;
use PHPUnit\Framework\TestCase;

use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use Test\Persistence\ProtoBuf\UserCreated;

use function Swoole\Coroutine\run;

class MysqlProviderTest extends TestCase
{
    public function tearDown(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
                $pool = new PDOPool(
                    (new PDOConfig())
                        ->withHost('127.0.0.1')
                        ->withPort(3306)
                        ->withDbName('sample')
                        ->withCharset('utf8mb4')
                        ->withUsername('user')
                        ->withPassword('passw@rd')
                );
                $pool->get()->exec('TRUNCATE journals;');
                $pool->get()->exec('TRUNCATE snapshots;');
                $pool->close();
            });
        });
    }

    public function testPersistEvent(): void
    {
        run(function () {
            \Swoole\Coroutine\go(function () {
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
            \Swoole\Coroutine\go(function () {
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
        return new MysqlProvider(
            new Dsn(
                '127.0.0.1',
                3306,
                'sample',
                'user',
                'passw@rd'
            ),
            new DefaultSchema(),
            3,
            ActorSystem::create()->getLogger()
        );
    }
}
