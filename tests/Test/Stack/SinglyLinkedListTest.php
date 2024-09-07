<?php

declare(strict_types=1);

namespace Test\Stack;

use Phluxor\Stack\SinglyLinkedList;
use PHPUnit\Framework\TestCase;

class SinglyLinkedListTest extends TestCase
{
    public function testPushStack(): void
    {
        $list = new SinglyLinkedList();
        $list->push(1);
        $list->push(2);

        $this->assertEquals(2, $list->get(0));
        $this->assertEquals(1, $list->get(1));
    }

    public function testPopStack(): void
    {
        $list = new SinglyLinkedList();
        $list->prepend(1);
        $list->prepend(2);

        $this->assertEquals(2, $list->pop());
        $this->assertEquals(1, $list->length());
        $this->assertEquals(1, $list->pop());
        $this->assertEquals(0, $list->length());
    }

    public function testEachPopStack(): void
    {
        $list = new SinglyLinkedList();
        $list->push(1);
        $list->push(2);
        $list->push(3);
        $size = $list->length();
        for ($i = 0; $i < $size; $i++) {
            $list->pop();
            $this->assertEquals($size - $i - 1, $list->length());
        }
    }
}
