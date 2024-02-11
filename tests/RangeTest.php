<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Remorhaz\IntRangeSets\Exception\InvalidRangeException;
use Remorhaz\IntRangeSets\Range;

#[CoversClass(Range::class)]
class RangeTest extends TestCase
{
    public function testConstruct_StartGreaterThanFinish_ThrowsException(): void
    {
        $this->expectException(InvalidRangeException::class);
        $this->expectExceptionMessage('Invalid range: [2, 1]');
        new Range(2, 1);
    }

    public function testGetStart_ConstructedWithStart_ReturnsSameValue(): void
    {
        $range = new Range(1);
        self::assertSame(1, $range->getStart());
    }

    public function testGetFinish_ConstructedWithoutFinish_ReturnsStartValue(): void
    {
        $range = new Range(1);
        self::assertSame(1, $range->getFinish());
    }

    #[DataProvider('providerFinish')]
    public function testGetFinish_ConstructedWithFinish_ReturnsSameValue(
        int $start,
        int $finish,
        int $expectedValue,
    ): void {
        $range = new Range($start, $finish);
        self::assertSame($expectedValue, $range->getFinish());
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function providerFinish(): iterable
    {
        return [
            'Finish equals start' => [1, 1, 1],
            'Finish greater than start' => [1, 2, 2],
        ];
    }

    #[DataProvider('providerLength')]
    public function testGetLength_Constructed_ReturnsMatchingValue(int $start, int $finish, int $expectedValue): void
    {
        $range = new Range($start, $finish);
        self::assertSame($expectedValue, $range->getLength());
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function providerLength(): iterable
    {
        return [
            'Finish equals start' => [1, 1, 1],
            'Finish greater than start' => [1, 2, 2],
        ];
    }

    #[DataProvider('providerEqualRanges')]
    public function testEquals_EqualRange_ReturnsTrue(
        int $firstStart,
        int $firstFinish,
        int $secondStart,
        int $secondFinish,
    ): void {
        $range = new Range($firstStart, $firstFinish);
        self::assertTrue($range->equals(new Range($secondStart, $secondFinish)));
    }

    /**
     * @return iterable<string, array{int, int, int, int}>
     */
    public static function providerEqualRanges(): iterable
    {
        return [
            'Start equals finish' => [1, 1, 1, 1],
            'Start not equals finish' => [1, 2, 1, 2],
        ];
    }

    #[DataProvider('providerNotEqualRanges')]
    public function testEquals_NotEqualRange_ReturnsFalse(
        int $firstStart,
        int $firstFinish,
        int $secondStart,
        int $secondFinish,
    ): void {
        $range = new Range($firstStart, $firstFinish);
        self::assertFalse($range->equals(new Range($secondStart, $secondFinish)));
    }

    /**
     * @return iterable<string, array{int, int, int, int}>
     */
    public static function providerNotEqualRanges(): iterable
    {
        return [
            'Different starts' => [1, 3, 2, 3],
            'Different finishes' => [1, 2, 1, 3],
            'Different starts and finishes' => [1, 3, 2, 4],
        ];
    }

    #[DataProvider('providerContainsValue')]
    public function testContainsValue_ValueInRange_ReturnsTrue(int $start, int $finish, int $value): void
    {
        $range = new Range($start, $finish);
        self::assertTrue($range->containsValue($value));
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function providerContainsValue(): iterable
    {
        return [
            'Same start, finish and value' => [1, 1, 1],
            'Different start and finish, value equals start' => [1, 2, 1],
            'Different start and finish, value equals finish' => [1, 2, 2],
            'Value between start and finish' => [1, 3, 2],
        ];
    }

    #[DataProvider('providerNotContainsValue')]
    public function testContainsValue_ValueNotInRange_ReturnsFalse(int $start, int $finish, int $value): void
    {
        $range = new Range($start, $finish);
        self::assertFalse($range->containsValue($value));
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function providerNotContainsValue(): iterable
    {
        return [
            'Value before start' => [2, 3, 1],
            'Value after finish start' => [1, 2, 3],
        ];
    }

    #[DataProvider('providerIntersects')]
    public function testIntersects_Constructed_ReturnsMatchingValue(
        int $firstStart,
        int $firstEnd,
        int $secondStart,
        int $secondEnd,
        bool $expectedValue,
    ): void {
        $firstRange = new Range($firstStart, $firstEnd);
        $secondRange = new Range($secondStart, $secondEnd);
        $actualValue = $firstRange->intersects($secondRange);
        self::assertSame($expectedValue, $actualValue);
    }

    /**
     * @return iterable<string, array{int, int, int, int, bool}>
     */
    public static function providerIntersects(): iterable
    {
        return [
            'Second range follows first' => [1, 2, 3, 4, false],
            'First range follows second' => [3, 4, 1, 2, false],
            'Second range partially overlaps first' => [2, 3, 1, 2, true],
            'First range partially overlaps second' => [1, 2, 2, 3, true],
            'Ranges are equal' => [1, 1, 1, 1, true],
            'Ranges starts are equal and first range ends before second range ends' => [1, 1, 1, 2, true],
            'Ranges starts are equal and second range ends before first range ends' => [1, 2, 1, 1, true],
            'Ranges finishes are equal and first range starts before second range starts' => [1, 2, 2, 2, true],
            'Ranges finishes are equal and second range starts before first range starts' => [2, 2, 1, 2, true],
            'First range fully contains second range' => [1, 3, 2, 2, true],
            'Second range fully contains first range' => [2, 2, 1, 3, true],
        ];
    }

    #[DataProvider('providerContains')]
    public function testContains_Constructed_ReturnsMatchingValue(
        int $firstStart,
        int $firstEnd,
        int $secondStart,
        int $secondEnd,
        bool $expectedValue,
    ): void {
        $firstRange = new Range($firstStart, $firstEnd);
        $secondRange = new Range($secondStart, $secondEnd);
        $actualValue = $firstRange->contains($secondRange);
        self::assertSame($expectedValue, $actualValue);
    }

    /**
     * @return iterable<string, array{int, int, int, bool}>
     */
    public static function providerContains(): iterable
    {
        return [
            'Second range follows first' => [1, 2, 3, 4, false],
            'First range follows second' => [3, 4, 1, 2, false],
            'Second range partially overlaps first' => [2, 3, 1, 2, false],
            'First range partially overlaps second' => [1, 2, 2, 3, false],
            'Ranges are equal' => [1, 1, 1, 1, true],
            'Ranges starts are equal and first range ends before second range ends' => [1, 1, 1, 2, false],
            'Ranges starts are equal and second range ends before first range ends' => [1, 2, 1, 1, true],
            'Ranges finishes are equal and first range starts before second range starts' => [1, 2, 2, 2, true],
            'Ranges finishes are equal and second range starts before first range starts' => [2, 2, 1, 2, false],
            'First range fully contains second range' => [1, 3, 2, 2, true],
            'Second range fully contains first range' => [2, 2, 1, 3, false],
        ];
    }

    #[DataProvider('providerFollows')]
    public function testFollows_Constructed_ReturnsMatchingResult(
        int $firstStart,
        int $firstEnd,
        int $secondStart,
        int $secondEnd,
        bool $expectedValue,
    ): void {
        $firstRange = new Range($firstStart, $firstEnd);
        $secondRange = new Range($secondStart, $secondEnd);
        $actualValue = $firstRange->follows($secondRange);
        self::assertSame($expectedValue, $actualValue);
    }

    /**
     * @return iterable<string, array{int, int, int, int, bool}>
     */
    public static function providerFollows(): iterable
    {
        return [
            'First range goes before second range' => [1, 2, 4, 5, false],
            'First range follows second range' => [1, 2, 3, 4, false],
            'Second range follows first range' => [3, 4, 1, 2, true],
            'First range partially intersects second range' => [1, 2, 2, 3, false],
            'Second range partially intersects first range' => [2, 3, 1, 2, false],
        ];
    }

    public function testAsRangeSet_Constructed_ReturnsRangeSetWithSameInstance(): void
    {
        $range = new Range(1, 2);
        $rangeSet = $range->asRangeSet();
        self::assertSame([$range], $rangeSet->getRanges());
    }

    public function testWithStart_ValidStart_ReturnsRangeWithNewStart(): void
    {
        $range = new Range(1, 3);
        self::assertSame(2, $range->withStart(2)->getStart());
    }

    public function testWithStart_ValidStart_ReturnsRangeWithSameFinish(): void
    {
        $range = new Range(1, 3);
        self::assertSame(3, $range->withStart(2)->getFinish());
    }

    public function testWithStart_ValidStart_ReturnsAnotherInstance(): void
    {
        $range = new Range(1, 3);
        self::assertNotSame($range, $range->withStart(2));
    }

    public function testWithStart_InvalidStart_ThrowsException(): void
    {
        $range = new Range(1, 3);
        $this->expectException(InvalidRangeException::class);
        $this->expectExceptionMessage('Invalid range: [4, 3]');
        $range->withStart(4);
    }

    public function testWithFinish_ValidFinish_ReturnsRangeWithNewFinish(): void
    {
        $range = new Range(1, 3);
        self::assertSame(2, $range->withFinish(2)->getFinish());
    }

    public function testWithFinish_ValidFinish_ReturnsRangeWithSameStart(): void
    {
        $range = new Range(1, 3);
        self::assertSame(1, $range->withFinish(2)->getStart());
    }

    public function testWithFinish_ValidFinish_ReturnsAnotherInstance(): void
    {
        $range = new Range(1, 3);
        self::assertNotSame($range, $range->withFinish(2));
    }

    public function testWithFinish_InvalidFinish_ThrowsException(): void
    {
        $range = new Range(1, 3);
        $this->expectException(InvalidRangeException::class);
        $this->expectExceptionMessage('Invalid range: [1, 0]');
        $range->withFinish(0);
    }
}
