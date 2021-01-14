<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

/**
 * Immutable non-empty range of integers.
 *
 * @psalm-immutable
 */
interface RangeInterface
{

    /**
     * @return int
     */
    public function getStart(): int;

    /**
     * @return int
     */
    public function getFinish(): int;

    /**
     * @return int
     */
    public function getLength(): int;

    /**
     * @param RangeInterface $range
     * @return bool
     */
    public function equals(RangeInterface $range): bool;

    /**
     * @param int $value
     * @return bool
     */
    public function containsValue(int $value): bool;

    /**
     * @param RangeInterface $range
     * @return bool
     */
    public function contains(RangeInterface $range): bool;

    /**
     * @param RangeInterface $range
     * @return bool
     */
    public function intersects(RangeInterface $range): bool;

    /**
     * @param RangeInterface $range
     * @return bool
     */
    public function follows(RangeInterface $range): bool;

    /**
     * @return RangeSetInterface
     */
    public function asRangeSet(): RangeSetInterface;

    /**
     * @param int $value
     * @return RangeInterface
     */
    public function withStart(int $value): RangeInterface;

    /**
     * @param int $value
     * @return RangeInterface
     */
    public function withFinish(int $value): RangeInterface;
}
