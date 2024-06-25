<?php

declare(strict_types=1);

namespace ActorSystem\Message;

use Phluxor\ActorSystem\Message\DetectAutoReceiveMessage;
use Phluxor\ActorSystem\Message\Restarting;
use Phluxor\ActorSystem\ProtoBuf\Stop;
use PHPUnit\Framework\TestCase;

class DetectAutoReceiveMessageTest extends TestCase
{
    public function testShouldReturnExpectedWhenAnyMessageIsPassed(): void
    {
        $messages = [
            [
                'message' => new Restarting(),
                'expected' => true,
            ],
            [
                'message' => new Stop(),
                'expected' => false,
            ],
            [
                'message' => "string",
                'expected' => false,
            ],
            [
                'message' => "Stop",
                'expected' => false,
            ]
        ];
        foreach ($messages as $message) {
            $autoReceiveMessage = new DetectAutoReceiveMessage($message['message']);
            $this->assertEquals($message['expected'], $autoReceiveMessage->isMatch());
        }
    }
}