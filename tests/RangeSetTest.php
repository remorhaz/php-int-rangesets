<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Test;

use PHPUnit\Framework\TestCase;
use Remorhaz\IntRangeSets\Range;
use Remorhaz\IntRangeSets\RangeSet;
use Remorhaz\IntRangeSets\RangeSetInterface;

/**
 * @covers \Remorhaz\IntRangeSets\RangeSet
 */
class RangeSetTest extends TestCase
{
    /**
     * @param list<list<int>> $rangesData
     * @param list<list<int>> $expectedValue
     * @dataProvider providerCreate
     */
    public function testCreate_GivenRanges_ReturnsMatchingRangeSet(array $rangesData, array $expectedValue): void
    {
        $rangeSet = RangeSet::create(...RangeSet::importRanges(...$rangesData));
        self::assertSame($expectedValue, $this->exportRangeSet($rangeSet));
    }

    /**
     * @return iterable<string, array{list<list<int>>, list<list<int>>}>
     */
    public static function providerCreate(): iterable
    {
        return [
            'No ranges' => [[], [], []],
            'Non-overlapping ranges in reversed order' => [[[4, 5], [1, 2]], [[1, 2], [4, 5]]],
            'Overlapping ranges' => [[[1, 3], [2, 4]], [[1, 4]]],
        ];
    }

    public function testWithRanges_GivenRangeSet_ReturnsAnotherInstance(): void
    {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges([1, 3]));
        $newRangeSet = $rangeSet->withRanges(...RangeSet::importRanges([2, 4]));
        self::assertNotSame($rangeSet, $newRangeSet);
    }

    /**
     * @param list<list<int>> $rangeData
     * @param list<list<int>> $rangeDataToMerge
     * @param list<list<int>> $expectedValue
     * @dataProvider providerMergedRanges
     */
    public function testWithRanges_GivenRangeSetAndRangesToMerge_ReturnsMatchingRangeSet(
        array $rangeData,
        array $rangeDataToMerge,
        array $expectedValue
    ): void {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$rangeData));
        $newRangeSet = $rangeSet->withRanges(...RangeSet::importRanges(...$rangeDataToMerge));
        self::assertSame($expectedValue, $this->exportRangeSet($newRangeSet));
    }

    public function providerMergedRanges(): array
    {
        return [
            'Empty range set, no ranges to merge' => [[], [], []],
            'Empty range set, single range to merge' => [[], [[1, 2]], [[1, 2]]],
            'Single range in set, no ranges to merge' => [[[1, 2]], [], [[1, 2]]],
            'Single range in set before range to merge' => [[[1, 2]], [[4, 5]], [[1, 2], [4, 5]]],
            'Single range in set after range to merge' => [[[4, 5]], [[1, 2]], [[1, 2], [4, 5]]],
            'Single range in set preceding range to merge' => [[[1, 2]], [[3, 4]], [[1, 4]]],
            'Single range in set following range to merge' => [[[3, 4]], [[1, 2]], [[1, 4]]],
            'Single range in set, start overlapped by range to merge' => [[[2, 3]], [[1, 2]], [[1, 3]]],
            'Single range in set, finish overlapped by range to merge' => [[[1, 2]], [[2, 3]], [[1, 3]]],
            'Single range in set, equal range to merge' => [[[1, 2]], [[1, 2]], [[1, 2]]],
            'Single range in set, shorter range with same start to merge' => [[[1, 3]], [[1, 2]], [[1, 3]]],
            'Single range in set, longer range with same start to merge' => [[[1, 2]], [[1, 3]], [[1, 3]]],
            'Single range in set, shorter range with same finish to merge' => [[[1, 3]], [[2, 3]], [[1, 3]]],
            'Single range in set, longer range with same finish to merge' => [[[2, 3]], [[1, 3]], [[1, 3]]],
            'Single range in set, fully contained range to merge' => [[[1, 3]], [[2, 2]], [[1, 3]]],
            'Single range in set, fully containing range to merge' => [[[2, 2]], [[1, 3]], [[1, 3]]],
            'Single range in set, range with fully contained start to merge' => [[[1, 3]], [[2, 4]], [[1, 4]]],
            'Single range in set, range with fully contained finish to merge' => [[[2, 4]], [[1, 3]], [[1, 4]]],
            'Single range i set, two ranges in wrong order to merge' => [[[1, 3]], [[6, 7], [5, 6]], [[1, 3], [5, 7]]],
        ];
    }

    public function testCreateUnion_GivenRangeSetAndRangeSetToMerge_ReturnsMatchingRangeSet(): void
    {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges([1, 3]));
        $union = $rangeSet->createUnion(RangeSet::createUnsafe(...RangeSet::importRanges([2, 4])));
        self::assertSame([[1, 4]], $this->exportRangeSet($union));
    }

    public function testCreateUnion_GivenRangeSet_ReturnsAnotherInstance(): void
    {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges([1, 3]));
        $union = $rangeSet->createUnion(RangeSet::createUnsafe(...RangeSet::importRanges([2, 4])));
        self::assertNotSame($rangeSet, $union);
    }

    public function testCreateUnion_GivenRangeSetToMerge_ReturnsAnotherInstance(): void
    {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges([1, 3]));
        $rangeSetToUnite = RangeSet::createUnsafe(...RangeSet::importRanges([2, 4]));
        $mergedRangeSet = $rangeSet->createUnion($rangeSetToUnite);
        self::assertNotSame($rangeSetToUnite, $mergedRangeSet);
    }

    /**
     * @param list<list<int>> $rangeData
     * @param list<list<int>> $rangeDataToMerge
     * @param list<list<int>> $expectedValue
     * @dataProvider providerSymmetricDifferenceRanges
     */
    public function testCreateSymmetricDifference_GivenRangeSetAndRangesToXor_ReturnsMatchingRangeSet(
        array $rangeData,
        array $rangeDataToMerge,
        array $expectedValue
    ): void {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$rangeData));
        $symmetricDifference = $rangeSet->createSymmetricDifference(
            RangeSet::createUnsafe(...RangeSet::importRanges(...$rangeDataToMerge))
        );
        self::assertSame($expectedValue, $this->exportRangeSet($symmetricDifference));
    }

    /**
     * @return iterable<string, array{list<list<int>>, list<list<int>>, list<list<int>>}>
     */
    public static function providerSymmetricDifferenceRanges(): iterable
    {
        return [
            "Empty range" => [[[1, 2]], [], [[1, 2]]],
            "Empty existing range" => [[], [[1, 2]], [[1, 2]]],
            "Range after existing range" => [[[1, 2]], [[4, 5]], [[1, 2], [4, 5]]],
            "Range before existing range" => [[[4, 5]], [[1, 2]], [[1, 2], [4, 5]]],
            "Range right before existing range" => [[[2, 5]], [[1]], [[1, 5]]],
            "Range partially before existing range" => [[[2, 5]], [[1, 3]], [[1], [4, 5]]],
            "Range entirely inside existing range" => [[[2, 5]], [[3]], [[2], [4, 5]]],
            "Range starts before existing range with matching ends" => [[[2, 5]], [[1, 5]], [[1]]],
            "Range starts before and ends after existing range" => [[[2, 5]], [[3]], [[2], [4, 5]]],
            "Range starts before and ends after all existing ranges" =>
                [[[2, 5], [7, 10]], [[1, 13]], [[1], [6], [11, 13]]],
            "Range partially intersects with two existing ranges" =>
                [[[2, 5], [7, 10]], [[3, 7]], [[2], [6], [8, 10]]],
        ];
    }

    /**
     * @param list<list<int>> $rangeData
     * @param list<list<int>> $rangeDataToMerge
     * @param list<list<int>> $expectedValue
     * @dataProvider providerIntersectionRanges
     */
    public function testCreateIntersection_GivenRangeSetAndRangesToAnd_ReturnsMatchingRangeSet(
        array $rangeData,
        array $rangeDataToMerge,
        array $expectedValue
    ): void {
        $rangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$rangeData));
        $intersection = $rangeSet->createIntersection(
            RangeSet::createUnsafe(...RangeSet::importRanges(...$rangeDataToMerge)),
        );
        self::assertSame($expectedValue, $this->exportRangeSet($intersection));
    }

    public function providerIntersectionRanges(): array
    {
        return [
            "Empty range" => [[[1, 2]], [], []],
            "Empty existing range" => [[], [[1, 2]], []],
            "Same range" => [[[1, 2]], [[1, 2]], [[1, 2]]],
            "Range ends after existing range with matching starts" => [[[1, 2]], [[1, 3]], [[1, 2]]],
            "Range ends after existing range with matching starts but before next range" =>
                [[[1, 2], [4, 5]], [[1, 4]], [[1, 2], [4]]],
            "Range ends before existing range with matching starts" => [[[1, 3]], [[1, 2]], [[1, 2]]],
            "Range after existing range" => [[[1, 2]], [[4, 5]], []],
            "Range right after existing range" => [[[1, 2]], [[3, 4]], []],
            "Range before existing range" => [[[4, 5]], [[1, 2]], []],
            "Range right before existing range" => [[[2, 5]], [[1]], []],
            "Range partially before existing range" => [[[2, 5]], [[1, 3]], [[2, 3]]],
            "Range entirely inside existing range" => [[[2, 5]], [[3]], [[3]]],
            "Range starts after existing range with matching ends" => [[[1, 5]], [[2, 5]], [[2, 5]]],
            "Range starts before existing range with matching ends" => [[[2, 5]], [[1, 5]], [[2, 5]]],
            "Range starts before and ends after existing range" => [[[2, 5]], [[3]], [[3]]],
            "Range starts before and ends after all existing ranges" =>
                [[[2, 5], [7, 10]], [[1, 13]], [[2, 5], [7, 10]]],
            "Range partially intersects with two existing ranges" =>
                [[[2, 5], [7, 10]], [[3, 7]], [[3, 5], [7]]],
            'Second range from first range partially intersects with first range from second range' =>
                [[[1], [3, 4]], [[2, 4]], [[3, 4]]],
        ];
    }

    public function testIsEmpty_EmptyRange_ReturnsTrue(): void
    {
        $rangeSet = RangeSet::create();
        self::assertTrue($rangeSet->isEmpty());
    }

    public function testIsEmpty_NonEmptyRange_ReturnsFalse(): void
    {
        $rangeSet = RangeSet::createUnsafe(new Range(1, 2));
        self::assertFalse($rangeSet->isEmpty());
    }

    /**
     * @param list<list<int>> $firstRangesData
     * @param list<list<int>> $secondRangesData
     * @dataProvider providerEqualRanges
     */
    public function testEquals_EqualRanges_ReturnsTrue(array $firstRangesData, array $secondRangesData): void
    {
        $firstRangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$firstRangesData));
        $secondRangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$secondRangesData));
        self::assertTrue($firstRangeSet->equals($secondRangeSet));
    }

    /**
     * @return iterable<string, array{list<list<int>>, list<list<int>>}>
     */
    public static function providerEqualRanges(): iterable
    {
        return [
            'Empty range sets' => [[], []],
            'Range sets with single range' => [[[1, 2]], [[1, 2]]],
            'Range sets with two ranges' => [[[1, 2], [4, 5]], [[1, 2], [4, 5]]],
        ];
    }

    /**
     * @param list<list<int>> $firstRangesData
     * @param list<list<int>> $secondRangesData
     * @dataProvider providerNotEqualRanges
     */
    public function testEquals_NotEqualRanges_ReturnsFalse(array $firstRangesData, array $secondRangesData): void
    {
        $firstRangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$firstRangesData));
        $secondRangeSet = RangeSet::createUnsafe(...RangeSet::importRanges(...$secondRangesData));
        self::assertFalse($firstRangeSet->equals($secondRangeSet));
    }

    /**
     * @return iterable<string, array{list<list<int>>, list<list<int>>}>
     */
    public static function providerNotEqualRanges(): iterable
    {
        return [
            'Sets with different amount of ranges' => [[[1, 2], [4, 5]], [[1, 2]]],
            'Sets with same amount of different ranges' => [[[4, 5]], [[1, 2]]],
        ];
    }

    /**
     * @param RangeSetInterface $rangeSet
     * @return list<list<int>>
     */
    private function exportRangeSet(RangeSetInterface $rangeSet): array
    {
        $result = [];
        foreach ($rangeSet->getRanges() as $range) {
            $result[] = $range->getLength() == 1
                ? [$range->getStart()]
                : [$range->getStart(), $range->getFinish()];
        }

        return $result;
    }
}
