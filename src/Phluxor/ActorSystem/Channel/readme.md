# Typed Channel

A typed channel is a channel that has a specific type of message that it can receive.  
This is useful for ensuring that the messages that are received are of the correct type.  

型付きチャネルとは、特定のメッセージタイプを受信できるチャネルのことです。    
これは、受信されるメッセージが正しいタイプであることを確認するために便利です。  

## Usage

```php
<?php

declare(strict_types=1);

namespace ActorSystem\Channel;

use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Channel\TypedChannel;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

function main(): void 
{
    run(function () {
        go(function () {
            $system = ActorSystem::create();
            $c = new TypedChannel($system, fn(mixed $message): bool => is_string($message));
            $system->root()->send($c->getRef(), "hello");
            $system->root()->send($c->getRef(), 12345);
            $system->root()->send($c->getRef(), "world");
            
            var_dump($c->result()); // hello
            var_dump($c->result()); // world
        });
    });
}
```

## notice

Swoole\Channelを使用していますが、期待する回数以上のメッセージを受信すると、  
デッドロックが発生します。  

テストコードなどで使用する場合は、  
`$c->result()`を呼び出した後に、`$c->close()`を呼び出してください。  

