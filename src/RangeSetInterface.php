<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

/**
 * Immutable set of integer ranges.
 */
interface RangeSetInterface
{

    /**
     * Returns new range set with ranges merged with ranges from another set.
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function merge(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set with ranges merged with given ranges.
     *
     * @param RangeInterface ...$ranges Order of the ranges can be arbitrary here.
     * @return RangeSetInterface
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface;

    /**
     * Returns new range set with XORed ranges, i. e. ranges that exist strictly in one of the sets (but not in both).
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function xor(RangeSetInterface $rangeSet): RangeSetInterface;

    /**
     * Returns new range set with ANDed ranges, i. e. ranges that exist in both sets.s
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function and(RangeSetInterface $rangeSet): RangeSetInterface;

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
