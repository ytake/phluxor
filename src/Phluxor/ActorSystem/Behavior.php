<?php

declare(strict_types=1);

namespace Phluxor\ActorSystem;

use Phluxor\ActorSystem\Context\ContextInterface;
use Phluxor\ActorSystem\Message\ActorInterface;
use Phluxor\ActorSystem\Message\ReceiveFunction;

class Behavior implements ActorInterface
{
    /** @var ReceiveFunction[] */
    private array $behaviors = [];

    /**
     * become changes the Actor's behavior to the new behavior
     * @param ReceiveFunction $receive
     * @return void
     */
    public function become(ReceiveFunction $receive): void
    {
        $this->clear();
        $this->push($receive);
    }

    /**
     * becomeStacked pushes the current behavior on the stack and then sets the new behavior
     * @param ReceiveFunction $receive
     * @return void
     */
    public function becomeStacked(ReceiveFunction $receive): void
    {
        $this->push($receive);
    }

    /**
     * unbecome clears the current behavior and reverts to the previous behavior
     * @return void
     */
    public function unbecome(): void
    {
        $this->pop();
    }

    /**
     * @param ContextInterface $context
     * @return void
     */
    public function receive(ContextInterface $context): void
    {
        $behavior = $this->peek();
        if ($behavior !== null) {
            $behavior->receive($context);
            return;
        }
        $context->logger()->error(
            "empty behavior called",
            [
                'pid' => $context->self()
            ]
        );
    }

    private function clear(): void
    {
        if (count($this->behaviors) === 0) {
            return;
        }

        foreach ($this->behaviors as &$behavior) {
            $behavior = null;
        }

        $this->behaviors = [];
    }

    private function peek(): ?ReceiveFunction
    {
        $length = $this->len();
        return $length > 0 ? $this->behaviors[$length - 1] : null;
    }

    private function push(ReceiveFunction $receive): void
    {
        $this->behaviors[] = $receive;
    }

    private function pop(): ReceiveFunction
    {
        $behavior = null;
        $length = $this->len();
        if ($length > 0) {
            $behavior = $this->behaviors[$length - 1];
            unset($this->behaviors[$length - 1]);
        }
        return $behavior;
    }

    private function len(): int
    {
        return count($this->behaviors);
    }
}
