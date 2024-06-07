<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Message;

use Closure;

readonly class Continuation implements SystemMessageInterface
{
    /**
     * @param mixed $message
     * @param ContinuationFunctionInterface|Closure(): void $function
     */
    public function __construct(
        private mixed $message,
        private ContinuationFunctionInterface|Closure $function
    ) {
    }

    /**
     * @return ContinuationFunctionInterface|Closure(): void
     */
    public function getFunction(): ContinuationFunctionInterface|Closure
    {
        return $this->function;
    }

    /**
     * @return mixed
     */
    public function getMessage(): mixed
    {
        return $this->message;
    }
}
