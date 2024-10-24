<?php

declare(strict_types=1);

namespace Test\Persistence\Sqlite;

use Google\Protobuf\Internal\Message;
use PDO;
use Phluxor\ActorSystem;
use Phluxor\Persistence\Sqlite\Connection;
use Phluxor\Persistence\Sqlite\DefaultSchema;
use Phluxor\Persistence\Sqlite\SqliteProvider;
use PHPUnit\Framework\TestCase;
use Test\Persistence\ProtoBuf\UserCreated;

use function Swoole\Coroutine\run;

class SqliteProviderTest extends TestCase
{
    private const string SQLITE_DB_PATH = '../../../sqlite/data/data.db';

    public function tearDown(): void
    {
        run(function () {
            go(function () {
                $path = $this->sqlitePath();
                $conn = new PDO("sqlite:$path");
                $conn->exec('DELETE FROM journals;');
                $conn->exec('DELETE FROM snapshots;');
                $conn = null;
            });
        });
    }

    public function testPersistEvent(): void
    {
        run(function () {
            go(function () {
                $provider = $this->sqliteProvider();
                $event = new UserCreated([
                    'userID' => 'test',
                    'userName' => 'test',
                    'email' => '',
                ]);
                for($i = 0; $i < 400; $i++) {
                    $provider->persistenceEvent('user', $i, $event);
                }
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
                $provider->getEvents('user', 399, 400, function (Message $e) use (&$processed) {
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
                $provider = $this->sqliteProvider();
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

    private function sqlitePath(): string
    {
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . self::SQLITE_DB_PATH);
        if(!$path) {
            throw new \RuntimeException();
        }
        return $path;
    }

    private function sqliteProvider(): SqliteProvider
    {
        $conn = new Connection($this->sqlitePath());
        return new SqliteProvider(
            $conn->proxy(),
            new DefaultSchema(),
            3,
            ActorSystem::create()->getLogger()
        );
    }
}
