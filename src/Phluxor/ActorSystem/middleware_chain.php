<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Closure;
use Phluxor\ActorSystem;
use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Context\ReceiverInterface;
use Phluxor\ActorSystem\Context\SenderInterface;
use Phluxor\ActorSystem\Context\SpawnerInterface;
use Phluxor\ActorSystem\Message\ContextDecoratorFunctionInterface;
use Phluxor\ActorSystem\Message\MessageEnvelope;
use Phluxor\ActorSystem\Message\ReceiverFunctionInterface;
use Phluxor\ActorSystem\Message\SenderFunctionInterface;
use Phluxor\ActorSystem\Props\ContextDecoratorInterface;
use Phluxor\ActorSystem\Props\ReceiverMiddlewareInterface;
use Phluxor\ActorSystem\Props\SenderMiddlewareInterface;
use Phluxor\ActorSystem\Props\SpawnMiddlewareInterface;

use function array_key_last;
use function count;

/**
 * @param Closure[]|ReceiverMiddlewareInterface[] $receiverMiddleware
 * @param Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface|null $lastReceiver
 * @return Closure(ReceiverInterface|ContextInterface, MessageEnvelope): void|ReceiverFunctionInterface|null
 */
function makeReceiverMiddlewareChain(
    array $receiverMiddleware,
    Closure|ReceiverFunctionInterface|null $lastReceiver
): Closure|ReceiverFunctionInterface|null {
    if (empty($receiverMiddleware)) {
        return null;
    }
    $h = $receiverMiddleware[array_key_last($receiverMiddleware)]($lastReceiver);
    for ($i = count($receiverMiddleware) - 2; $i >= 0; $i--) {
        $h = $receiverMiddleware[$i]($h);
    }
    return $h;
}

/**
 * @param Closure[]|SenderMiddlewareInterface[] $senderMiddleware
 * @param Closure(SenderInterface|ContextInterface, Ref, MessageEnvelope): void|SenderFunctionInterface|null $lastSender
 * @return Closure(SenderInterface|ContextInterface, Ref, MessageEnvelope): void|SenderFunctionInterface|null
 */
function makeSenderMiddlewareChain(
    array $senderMiddleware,
    Closure|SenderFunctionInterface|null $lastSender
): Closure|SenderFunctionInterface|null {
    if (empty($senderMiddleware)) {
        return null;
    }
    $h = $senderMiddleware[array_key_last($senderMiddleware)]($lastSender);
    for ($i = count($senderMiddleware) - 2; $i >= 0; $i--) {
        $h = $senderMiddleware[$i]($h);
    }
    return $h;
}

/**
 * @param Closure[]|ContextDecoratorInterface[] $decorators
 * @param Closure(ContextInterface): ContextInterface|ContextDecoratorFunctionInterface|null $lastDecorator
 * @return Closure(ContextInterface): ContextInterface|ContextDecoratorFunctionInterface|null
 */
function makeContextDecoratorChain(
    array $decorators,
    Closure|ContextDecoratorFunctionInterface|null $lastDecorator
): Closure|ContextDecoratorFunctionInterface|null {
    if (empty($decorators)) {
        return null;
    }
    $h = $decorators[array_key_last($decorators)]($lastDecorator);
    for ($i = count($decorators) - 2; $i >= 0; $i--) {
        $h = $decorators[$i]($h);
    }
    return $h;
}

/**
 * @param Closure[]|SpawnMiddlewareInterface[] $spawnMiddleware
 * @param Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null $lastSpawn
 * @return Closure(ActorSystem, string, Props, SpawnerInterface): SpawnResult|SpawnFunctionInterface|null
 */
function makeSpawnMiddlewareChain(
    array $spawnMiddleware,
    Closure|SpawnFunctionInterface|null $lastSpawn
): Closure|SpawnFunctionInterface|null {
    if (empty($spawnMiddleware)) {
        return null;
    }
    $h = $spawnMiddleware[array_key_last($spawnMiddleware)]($lastSpawn);
    for ($i = count($spawnMiddleware) - 2; $i >= 0; $i--) {
        $h = $spawnMiddleware[$i]($h);
    }
    return $h;
}
