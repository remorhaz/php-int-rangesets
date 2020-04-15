<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Test;

use PHPUnit\Framework\TestCase;
use Remorhaz\IntRangeSets\Range;
use Remorhaz\IntRangeSets\RangeInterface;
use Remorhaz\IntRangeSets\RangeSet;
use Remorhaz\IntRangeSets\RangeSetInterface;

/**
 * @covers \Remorhaz\IntRangeSets\RangeSet
 */
class RangeSetTest extends TestCase
{

    public function testWithRanges_GivenRangeSet_ReturnsAnotherInstance(): void
    {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges([1, 3]));
        $mergedRangeSet = $rangeSet->withRanges(...$this->importRanges([2, 4]));
        self::assertNotSame($rangeSet, $mergedRangeSet);
    }

    /**
     * @param int[][] $rangeData
     * @param int[][] $rangeDataToMerge
     * @param int[][] $expectedValue
     * @dataProvider providerMergedRanges
     */
    public function testWithRanges_GivenRangeSetAndRangesToMerge_ReturnsMatchingRangeSet(
        array $rangeData,
        array $rangeDataToMerge,
        array $expectedValue
    ): void {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges(...$rangeData));
        $mergedRange = $rangeSet->withRanges(...$this->importRanges(...$rangeDataToMerge));
        self::assertSame($expectedValue, $this->exportRangeSet($mergedRange));
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

    public function testMerge_GivenRangeSetAndRangeSetToMerge_ReturnsMatchingRangeSet(): void
    {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges([1, 3]));
        $mergedRangeSet = $rangeSet->merge(RangeSet::createUnsafe(...$this->importRanges([2, 4])));
        self::assertSame([[1, 4]], $this->exportRangeSet($mergedRangeSet));
    }

    public function testMerge_GivenRangeSet_ReturnsAnotherInstance(): void
    {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges([1, 3]));
        $mergedRangeSet = $rangeSet->merge(RangeSet::createUnsafe(...$this->importRanges([2, 4])));
        self::assertNotSame($rangeSet, $mergedRangeSet);
    }

    public function testMerge_GivenRangeSetToMerge_ReturnsAnotherInstance(): void
    {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges([1, 3]));
        $rangeSetToMerge = RangeSet::createUnsafe(...$this->importRanges([2, 4]));
        $mergedRangeSet = $rangeSet->merge($rangeSetToMerge);
        self::assertNotSame($rangeSetToMerge, $mergedRangeSet);
    }

    /**
     * @param int[][] $rangeData
     * @param int[][] $rangeDataToMerge
     * @param int[][] $expectedValue
     * @dataProvider providerXoredRanges
     */
    public function testXor_GivenRangeSetAndRangesToXor_ReturnsMatchingRangeSet(
        array $rangeData,
        array $rangeDataToMerge,
        array $expectedValue
    ): void {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges(...$rangeData));
        $xoredRange = $rangeSet->xor(RangeSet::createUnsafe(...$this->importRanges(...$rangeDataToMerge)));
        self::assertSame($expectedValue, $this->exportRangeSet($xoredRange));
    }

    public function providerXoredRanges(): array
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
     * @param int[][] $rangeData
     * @param int[][] $rangeDataToMerge
     * @param int[][] $expectedValue
     * @dataProvider providerAndedRanges
     */
    public function testAnd_GivenRangeSetAndRangesToAnd_ReturnsMatchingRangeSet(
        array $rangeData,
        array $rangeDataToMerge,
        array $expectedValue
    ): void {
        $rangeSet = RangeSet::createUnsafe(...$this->importRanges(...$rangeData));
        $xoredRange = $rangeSet->and(RangeSet::createUnsafe(...$this->importRanges(...$rangeDataToMerge)));
        self::assertSame($expectedValue, $this->exportRangeSet($xoredRange));
    }

    public function providerAndedRanges(): array
    {
        return [
            "Empty range" => [[[1, 2]], [], []],
            "Empty existing range" => [[], [[1, 2]], []],
            "Same range" => [[[1, 2]], [[1, 2]], [[1, 2]]],
            "Range ends after existing range with matching starts" => [[[1, 2]], [[1, 3]], [[1, 2]]],
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
        ];
    }

    public function testIsEmpty_EmptyRange_ReturnsTrue(): void
    {
        $rangeSet = new RangeSet();
        self::assertTrue($rangeSet->isEmpty());
    }

    public function testIsEmpty_NonEmptyRange_ReturnsFalse(): void
    {
        $rangeSet = RangeSet::createUnsafe(new Range(1, 2));
        self::assertFalse($rangeSet->isEmpty());
    }

    /**
     * @param int[] ...$data
     * @return RangeInterface[]
     */
    private function importRanges(array ...$data): array
    {
        $ranges = [];
        foreach ($data as $rangeData) {
            $ranges[] = new Range(...$rangeData);
        }

        return $ranges;
    }

    /**
     * @param RangeSetInterface $rangeSet
     * @return int[][]
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
