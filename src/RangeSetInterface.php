<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

/**
 * Immutable set of integer ranges.
 *
 * @psalm-immutable
 */
interface RangeSetInterface
{

    /**
     * Returns new range set that is a union of current and given sets ($this ∪ $rangeSet).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function createUnion(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set with ranges merged with given ranges.
     *
     * @param RangeInterface ...$ranges Order of the ranges can be arbitrary here.
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface;

    /**
     * Returns new range set that is a symmetric difference of current and given sets ($this ∆ $rangeSet).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function createSymmetricDifference(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set that is an intersection of current and gives sets ($this ∩ $rangeSet).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function createIntersection(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns TRUE if both sets contain same ranges.
     *
     * @param RangeSetInterface $rangeSet
     * @return bool
     * @psalm-pure
     */
    public function equals(RangeSetInterface $rangeSet): bool;

    /**
     * Returns list of ranges contained in set.
     *
     * @return RangeInterface[]
     * @psalm-pure
     */
    public function getRanges(): array;

    /**
     * Returns TRUE if range set contains no ranges.
     *
     * @return bool
     * @psalm-pure
     */
    public function isEmpty(): bool;
}
