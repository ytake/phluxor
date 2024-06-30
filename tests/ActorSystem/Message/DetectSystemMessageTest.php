<?php

declare(strict_types=1);

namespace Test\ActorSystem\Message;

use Phluxor\ActorSystem\Message\Continuation;
use Phluxor\ActorSystem\Message\DetectSystemMessage;
use Phluxor\ActorSystem\Message\Restarting;
use Phluxor\ActorSystem\ProtoBuf\Stop;
use PHPUnit\Framework\TestCase;

class DetectSystemMessageTest extends TestCase
{
    public function testShouldReturnExpectedWhenAnyMessageIsPassed(): void
    {
        $messages = [
            [
                'message' => new Restarting(),
                'expected' => false,
            ],
            [
                'message' => new Stop(),
                'expected' => true,
            ],
            [
                'message' => "string",
                'expected' => false,
            ],
            [
                'message' => "Stop",
                'expected' => false,
            ],
            [
                'message' => new Continuation('', fn() => null),
                'expected' => true,
            ]
        ];
        foreach ($messages as $message) {
            $autoReceiveMessage = new DetectSystemMessage($message['message']);
            $this->assertEquals($message['expected'], $autoReceiveMessage->isMatch());
        }
    }
}