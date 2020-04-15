<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Test\Exception;

use Exception;
use PHPUnit\Framework\TestCase;
use Remorhaz\IntRangeSets\Exception\RangeNotPickedException;

/**
 * @covers \Remorhaz\IntRangeSets\Exception\RangeNotPickedException
 */
class RangeNotPickedExceptionTest extends TestCase
{

    public function testGetMessage_Constructed_ReturnsMatchingValue(): void
    {
        $exception = new RangeNotPickedException();
        self::assertSame('Failed to pick range from query', $exception->getMessage());
    }

    public function testGetCode_Always_ReturnsZero(): void
    {
        $exception = new RangeNotPickedException();
        self::assertSame(0, $exception->getCode());
    }

    public function testGetPrevious_ConstructedWithoutPrevious_ReturnsNull(): void
    {
        $exception = new RangeNotPickedException();
        self::assertNull($exception->getPrevious());
    }

    public function testGetPrevios_ConstructedWithPrevious_ReturnsSameInstance(): void
    {
        $previous = new Exception();
        $exception = new RangeNotPickedException($previous);
        self::assertSame($previous, $exception->getPrevious());
    }
}
