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

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $finish;

    /**
     * @param int      $start  First value of the range.
     * @param int|null $finish Last value of the range. If null than equals first value, otherwise must be greater or
     *                         equal than first value.
     * @throws Exception\InvalidRangeException
     */
    public function __construct(int $start, ?int $finish = null)
    {
        if (!isset($finish)) {
            $finish = $start;
        } elseif ($finish < $start) {
            throw new Exception\InvalidRangeException($start, $finish);
        }

        $this->start = $start;
        $this->finish = $finish;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     * @psalm-pure
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     * @psalm-pure
     */
    public function getFinish(): int
    {
        return $this->finish;
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     * @psalm-pure
     */
    public function getLength(): int
    {
        return $this->finish - $this->start + 1;
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function equals(RangeInterface $range): bool
    {
        return $range->getStart() == $this->start && $range->getFinish() == $this->finish;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $value
     * @return bool
     * @psalm-pure
     */
    public function containsValue(int $value): bool
    {
        return $this->start <= $value && $value <= $this->finish;
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function contains(RangeInterface $range): bool
    {
        return $range->getStart() >= $this->start && $range->getFinish() <= $this->finish;
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function intersects(RangeInterface $range): bool
    {
        return $this->finish >= $range->getStart() && $range->getFinish() >= $this->start;
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeInterface $range
     * @return bool
     * @psalm-pure
     */
    public function follows(RangeInterface $range): bool
    {
        return $this->start == $range->getFinish() + 1;
    }

    /**
     * {@inheritDoc}
     *
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function asRangeSet(): RangeSetInterface
    {
        return RangeSet::createUnsafe($this);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $value
     * @return RangeInterface
     * @psalm-pure
     */
    public function withStart(int $value): RangeInterface
    {
        return new self($value, $this->finish);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $value
     * @return RangeInterface
     * @psalm-pure
     */
    public function withFinish(int $value): RangeInterface
    {
        return new self($this->start, $value);
    }
}
