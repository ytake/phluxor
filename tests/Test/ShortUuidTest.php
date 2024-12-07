<?php

declare(strict_types=1);

namespace Test;

use Brick\Math\Exception\DivisionByZeroException;
use Brick\Math\Exception\MathException;
use Phluxor\ShortUuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ShortUuidTest extends TestCase
{
    private ShortUuid $shortUuid;

    public function setUp() : void
    {
        $this->shortUuid = new ShortUuid();
    }

    /**
     * @dataProvider uuidProvider
     */
    public function testShouldEncodeAGivenUuid(
        UuidInterface $uuid,
        string $expectedShortUuid
    ): void {
        $shortUuid = $this->shortUuid->encode($uuid);
        $this->assertSame($expectedShortUuid, $shortUuid);
    }

    /**
     * @return array<int, array<string|UuidInterface>>
     */
    public static function uuidProvider(): array
    {
        return [
            [Uuid::fromString('4e52c919-513e-4562-9248-7dd612c6c1ca'), 'fpfyRTmt6XeE9ehEKZ5LwF'],
            [Uuid::fromString('59a3e9ab-6b99-4936-928a-d8b465dd41e0'), 'BnxtX5wGumMUWXmnbey6xH'],
        ];
    }

    /**
     * @dataProvider shortUuidProvider
     */
    public function testShouldDecodeAGivenShortUuid(
        string $shortUuid,
        UuidInterface $expectedUuid
    ): void {
        $uuid = $this->shortUuid->decode($shortUuid);
        $this->assertTrue($expectedUuid->equals($uuid));
    }

    /**
     * @return array<int, array<string|UuidInterface>>
     */
    public static function shortUuidProvider(): array
    {
        return [
            ['fpfyRTmt6XeE9ehEKZ5LwF', Uuid::fromString('4e52c919-513e-4562-9248-7dd612c6c1ca')],
            ['BnxtX5wGumMUWXmnbey6xH', Uuid::fromString('59a3e9ab-6b99-4936-928a-d8b465dd41e0')],
        ];
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public function testShouldGenerateAShortUuid1(): void
    {
        $shortUuid = ShortUuid::uuid1();
        $this->assertLessThanOrEqual(22, strlen($shortUuid));
        $this->assertGreaterThanOrEqual(20, strlen($shortUuid));
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public function testShouldGenerateAShortUuid4(): void
    {
        $shortUuid = ShortUuid::uuid4();
        $this->assertLessThanOrEqual(22, strlen($shortUuid));
        $this->assertGreaterThanOrEqual(20, strlen($shortUuid));
    }

    /**
     * @throws MathException
     * @throws DivisionByZeroException
     */
    public function testShouldGenerateAShortUuid5(): void
    {
        $shortUuid = ShortUuid::uuid5(Uuid::NAMESPACE_DNS, 'phluxor.dev');
        $this->assertLessThanOrEqual(22, strlen($shortUuid));
        $this->assertGreaterThanOrEqual(20, strlen($shortUuid));
    }
}
