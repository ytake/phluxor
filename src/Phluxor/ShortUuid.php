<?php

declare(strict_types=1);

namespace Phluxor;

use Brick\Math\BigInteger;
use Brick\Math\Exception\DivisionByZeroException;
use Brick\Math\RoundingMode;
use Brick\Math\Exception\MathException;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ShortUuid
{
    /**
     * @var array|string[]
     */
    private array $alphabet = [
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'J',
        'K',
        'L',
        'M',
        'N',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];

    private int $alphabetLength = 57;

    /**
     * @param string[]|null $alphabet
     */
    public function __construct(?array $alphabet = null)
    {
        if (null !== $alphabet) {
            $this->alphabet = $alphabet;
            $this->alphabetLength = count($alphabet);
        }
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public static function uuid1(
        int|null|Hexadecimal|string $node = null,
        ?int $clockSeq = null
    ): string {
        $uuid = Uuid::uuid1($node, $clockSeq);
        $shortUuid = new self();
        return $shortUuid->encode($uuid);
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public static function uuid4(): string
    {
        $uuid = Uuid::uuid4();
        $shortUuid = new self();
        return $shortUuid->encode($uuid);
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public static function uuid5(string $ns, string $name): string
    {
        $uuid = Uuid::uuid5($ns, $name);
        $shortUuid = new self();
        return $shortUuid->encode($uuid);
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public function encode(UuidInterface $uuid): string
    {
        $uuidInteger = BigInteger::of((string)$uuid->getInteger());
        return $this->numToString($uuidInteger);
    }

    /**
     * @throws MathException
     */
    public function decode(string $shortUuid): UuidInterface
    {
        return Uuid::fromInteger((string)$this->stringToNum($shortUuid));
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    private function numToString(BigInteger $number): string
    {
        $output = '';
        while ($number->isPositive()) {
            $previousNumber = clone $number;
            $number = $number->dividedBy($this->alphabetLength, RoundingMode::DOWN);
            $digit = $previousNumber->mod($this->alphabetLength);
            $output .= $this->alphabet[$digit->toInt()];
        }
        return $output;
    }

    /**
     * @throws MathException
     */
    private function stringToNum(string $string): BigInteger
    {
        $number = BigInteger::of(0);
        foreach (str_split(strrev($string)) as $char) {
            $number = $this->updateNumber(
                $number,
                $this->validateCharacter($char, $this->alphabet),
                $this->alphabetLength
            );
        }
        return $number;
    }

    /**
     * @param mixed $char
     * @param string[] $alphabet
     * @return int|string
     */
    private function validateCharacter(mixed $char, array $alphabet): int|string
    {
        $index = array_search($char, $alphabet);
        if ($index === false) {
            throw new \InvalidArgumentException('Invalid character found: ' . $char);
        }
        return $index;
    }

    /**
     * @throws MathException
     */
    private function updateNumber(
        BigInteger $number,
        int|string $index,
        int $alphabetLength
    ): BigInteger {
        return $number->multipliedBy($alphabetLength)
            ->plus($index);
    }

    /**
     * @return string[]
     */
    public function getAlphabet(): array
    {
        return $this->alphabet;
    }
}
