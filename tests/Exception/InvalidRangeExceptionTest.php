<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Test\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Remorhaz\IntRangeSets\Exception\InvalidRangeException;

/**
 * @covers \Remorhaz\IntRangeSets\Exception\InvalidRangeException
 */
class InvalidRangeExceptionTest extends TestCase
{

    public function testGetMessage_Constructed_ReturnsMatchingValue(): void
    {
        $exception = new InvalidRangeException(1, 2);
        self::assertSame('Invalid range: [1, 2]', $exception->getMessage());
    }

    public function testGetStart_ConstructedWithValue_ReturnsSameValue(): void
    {
        $exception = new InvalidRangeException(1, 2);
        self::assertSame(1, $exception->getStart());
    }

    public function testGetFinish_ConstructedWithValue_ReturnsSameValue(): void
    {
        $exception = new InvalidRangeException(1, 2);
        self::assertSame(2, $exception->getFinish());
    }

    public function testGetCode_Always_ReturnsZero(): void
    {
        $exception = new InvalidRangeException(1, 2);
        self::assertSame(0, $exception->getCode());
    }

    public function testGetPrevious_ConstructedWithoutPrevious_ReturnsNull(): void
    {
        $exception = new InvalidRangeException(1, 2);
        self::assertNull($exception->getPrevious());
    }

    public function testGetPrevious_ConstructedWithPrevious_ReturnsSameInstance(): void
    {
        $previous = new Exception();
        $exception = new InvalidRangeException(1, 2, $previous);
        self::assertSame($previous, $exception->getPrevious());
    }
}
