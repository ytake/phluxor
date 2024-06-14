<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem\Context;

use DateInterval;
use Phluxor\ActorSystem\Future;
use Phluxor\ActorSystem\Ref;
use Phluxor\ActorSystem\ReenterAfterInterface;

interface BasePartInterface
{
    /**
     * returns the current timeout
     * @return DateInterval
     */
    public function receiveTimeout(): DateInterval;

    /**
     * returns a slice of the actors children
     * @return Ref[]
     */
    public function children(): array;

    /**
     * sends a response to the current `Sender`
     * @param mixed $response
     * @return void
     */
    public function respond(mixed $response): void;

    /**
     * stashes the current message on a stack for reprocessing when the actor restarts
     * @return void
     */
    public function stash(): void;

    /**
     * registers the actor as a monitor for the specified Ref
     * @param Ref $pid
     * @return void
     */
    public function watch(Ref $pid): void;

    /**
     * unregisters the actor as a monitor for the specified Ref
     * @param Ref $pid
     * @return void
     */
    public function unwatch(Ref $pid): void;

    /**
     * @param DateInterval $dateInterval
     * @return void
     */
    public function setReceiveTimeout(DateInterval $dateInterval): void;

    /**
     * @return void
     */
    public function cancelReceiveTimeout(): void;

    /**
     * forwards current message to the given Ref
     * @param Ref $pid
     * @return void
     */
    public function forward(Ref $pid): void;

    /**
     * Executes the given Future and reenters the current method after the Future has completed.
     *
     * @param Future $future The Future to execute.
     * @param ReenterAfterInterface $reenterAfter The ReenterAfterInterface object that defines how the current method should be reentered.
     * @throws \Throwable If an exception occurs while executing the Future.
     */
    public function reenterAfter(Future $future, ReenterAfterInterface $reenterAfter): void;
}
