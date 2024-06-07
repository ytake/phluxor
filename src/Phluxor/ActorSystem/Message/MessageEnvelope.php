<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Phluxor\ActorSystem\Pid;
use Phluxor\ActorSystem\QueueResult;
use Phluxor\ActorSystem\ReadonlyMessageHeaderInterface;

use function Swoole\Coroutine\Http\request;

readonly class MessageEnvelope
{
    /**
     * @param MessageHeader|null $header
     * @param mixed|null $message
     * @param Pid|null $sender
     */
    public function __construct(
        private MessageHeader|null $header = null,
        private mixed $message = null,
        private Pid|null $sender = null
    ) {
    }

    public function getSender(): Pid|null
    {
        return $this->sender;
    }

    /**
     * @return mixed
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }

    public function getHeader(string $key): string
    {
        if ($this->header === null) {
            return '';
        }
        return $this->header->get($key) ?? '';
    }

    public function setHeader(string $key, string $value): void
    {
        $this->header?->set($key, $value);
    }

    public static function wrapEnvelope(mixed $message): MessageEnvelope
    {
        if ($message instanceof MessageEnvelope) {
            return $message;
        }
        return new MessageEnvelope(new MessageHeader(), $message, null);
    }

    /**
     * @param mixed $message
     * @return array{header: ReadonlyMessageHeaderInterface|null, message: mixed, sender: Pid|null}
     */
    public static function unwrapEnvelope(mixed $message): array
    {
        if ($message instanceof MessageEnvelope) {
            return [
                'header' => $message->header,
                'message' => $message->message,
                'sender' => $message->sender
            ];
        }
        if ($message instanceof QueueResult) {
            return [
                'header' => null,
                'message' => $message->value(),
                'sender' => null
            ];
        }
        return [
            'header' => null,
            'message' => $message,
            'sender' => null
        ];
    }

    /**
     * @param mixed $message
     * @return ReadonlyMessageHeaderInterface
     */
    public static function unwrapEnvelopeHeader(mixed $message): ReadonlyMessageHeaderInterface
    {
        if ($message instanceof QueueResult) {
            $msg = $message->value();
            if ($msg instanceof MessageEnvelope) {
                return $msg->header ?? new MessageHeader();
            }
            return new MessageHeader();
        }
        if ($message instanceof MessageEnvelope) {
            return $message->header ?? new MessageHeader();
        }
        return new MessageHeader();
    }

    /**
     * @param mixed $message
     * @return mixed
     */
    public static function unwrapEnvelopeMessage(mixed $message): mixed
    {
        if ($message instanceof QueueResult) {
            $msg = $message->value();
            if ($msg instanceof MessageEnvelope) {
                return $msg->message;
            }
            return $msg;
        }
        if ($message instanceof MessageEnvelope) {
            if ($message->message instanceof QueueResult) {
                return $message->message->value();
            }
            return $message->getMessage();
        }
        return $message;
    }

    /**
     * @param mixed $message
     * @return Pid|null
     */
    public static function unwrapEnvelopeSender(mixed $message): Pid|null
    {
        if ($message instanceof QueueResult) {
            $msg = $message->value();
            if ($msg instanceof MessageEnvelope) {
                return $msg->sender;
            }
            return null;
        }
        if ($message instanceof MessageEnvelope) {
            return $message->sender;
        }
        return null;
    }
}
