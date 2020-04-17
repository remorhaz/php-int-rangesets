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
     * @psalm-pure
     */
    public function getStart(): int;

    /**
     * @return int
     * @psalm-pure
     */
    public function getFinish(): int;

    /**
     * @return int
     * @psalm-pure
     */
    public function getLength(): int;

    /**
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function equals(RangeInterface $range): bool;

    /**
     * @param int $value
     * @return bool
     * @psalm-pure
     */
    public function containsValue(int $value): bool;

    /**
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function contains(RangeInterface $range): bool;

    /**
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function intersects(RangeInterface $range): bool;

    /**
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function follows(RangeInterface $range): bool;

    /**
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function asRangeSet(): RangeSetInterface;

    /**
     * @param int $value
     * @return RangeInterface
     * @psalm-pure
     */
    public function withStart(int $value): RangeInterface;

    /**
     * @param int $value
     * @return RangeInterface
     * @psalm-pure
     */
    public function withFinish(int $value): RangeInterface;
}
