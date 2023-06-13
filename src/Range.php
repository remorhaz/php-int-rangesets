<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

/**
 * Immutable non-empty range of integers.
 *
 * @psalm-immutable
 */
final class Range implements RangeInterface
{
    private int $finish;

    /**
     * @param int      $start  First value of the range.
     * @param int|null $finish Last value of the range. If null than equals first value, otherwise must be greater or
     *                         equal than first value.
     * @throws Exception\InvalidRangeException
     */
    public function __construct(
        private int $start,
        ?int $finish = null,
    ) {
        $this->finish = $finish ?? $this->start;
        if ($this->finish < $this->start) {
            throw new Exception\InvalidRangeException($this->start, $this->finish);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * {@inheritDoc}
     */
    public function getFinish(): int
    {
        return $this->finish;
    }

    /**
     * {@inheritDoc}
     */
    public function getLength(): int
    {
        return $this->finish - $this->start + 1;
    }

    /**
     * {@inheritDoc}
     */
    public function equals(RangeInterface $range): bool
    {
        return $range->getStart() == $this->start && $range->getFinish() == $this->finish;
    }

    /**
     * {@inheritDoc}
     */
    public function containsValue(int $value): bool
    {
        return $this->start <= $value && $value <= $this->finish;
    }

    /**
     * {@inheritDoc}
     */
    public function contains(RangeInterface $range): bool
    {
        return $range->getStart() >= $this->start && $range->getFinish() <= $this->finish;
    }

    /**
     * {@inheritDoc}
     */
    public function intersects(RangeInterface $range): bool
    {
        return $this->finish >= $range->getStart() && $range->getFinish() >= $this->start;
    }

    /**
     * {@inheritDoc}
     */
    public function follows(RangeInterface $range): bool
    {
        return $this->start == $range->getFinish() + 1;
    }

    /**
     * {@inheritDoc}
     */
    public function asRangeSet(): RangeSetInterface
    {
        return RangeSet::createUnsafe($this);
    }

    /**
     * {@inheritDoc}
     */
    public function withStart(int $value): RangeInterface
    {
        return new self($value, $this->finish);
    }

    /**
     * {@inheritDoc}
     */
    public function withFinish(int $value): RangeInterface
    {
        return new self($this->start, $value);
    }
}
