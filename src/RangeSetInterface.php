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
     * @psalm-pure
     */
    public function createUnion(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set with ranges merged with given ranges.
     *
     * @psalm-pure
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface;

    /**
     * Returns new range set that is a symmetric difference of current and given sets ($this ∆ $rangeSet).
     *
     * @psalm-pure
     */
    public function createSymmetricDifference(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set that is an intersection of current and gives sets ($this ∩ $rangeSet).
     *
     * @psalm-pure
     */
    public function createIntersection(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns TRUE if both sets contain same ranges.
     *
     * @psalm-pure
     */
    public function equals(RangeSetInterface $rangeSet): bool;

    /**
     * Returns list of ranges contained in set.
     *
     * @return list<RangeInterface>
     * @psalm-pure
     */
    public function getRanges(): array;

    /**
     * Returns TRUE if range set contains no ranges.
     *
     * @psalm-pure
     */
    public function isEmpty(): bool;
}
