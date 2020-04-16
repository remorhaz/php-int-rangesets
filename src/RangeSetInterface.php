<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

/**
 * Immutable set of integer ranges.
 */
interface RangeSetInterface
{

    /**
     * Returns new range set that is a union of current and given sets ($this ∪ $rangeSet).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function createUnion(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set with ranges merged with given ranges.
     *
     * @param RangeInterface ...$ranges Order of the ranges can be arbitrary here.
     * @return RangeSetInterface
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface;

    /**
     * Returns new range set that is a symmetric difference of current and given sets ($this ∆ $rangeSet).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function createSymmetricDifference(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set that is an intersection of current and gives sets ($this ∩ $rangeSet).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function createIntersection(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns list of ranges contained in set.
     *
     * @return RangeInterface[]
     */
    public function getRanges(): array;

    /**
     * Returns TRUE if range set contains no ranges.
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
