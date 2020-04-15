<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

use function array_search;
use function usort;

/**
 * Immutable set of integer ranges.
 */
final class RangeSet implements RangeSetInterface
{

    /**
     * @var RangeInterface[]
     */
    private $ranges = [];

    /**
     * Provided ranges must be sorted, must not overlap and must not follow each other without gaps.
     * Warning: no checks are made! Use {@see RangeSet::merge()} to create set from arbitrary list of ranges.
     *
     * @param RangeInterface ...$ranges
     * @return static
     */
    public static function createUnsafe(RangeInterface ...$ranges): self
    {
        $rangeSet = new self();
        $rangeSet->ranges = $ranges;

        return $rangeSet;
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function merge(RangeSetInterface $rangeSet): RangeSetInterface
    {
        return $this->mergeRanges(...$rangeSet->getRanges());
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeInterface ...$ranges
     * @return RangeSetInterface
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface
    {
        return $this->mergeRanges(...$this->getSortedRanges(...$ranges));
    }

    /**
     * @param RangeInterface ...$ranges
     * @return RangeInterface[]
     * @psalm-return array<int,RangeInterface>
     */
    private function getSortedRanges(RangeInterface ...$ranges): array
    {
        usort($ranges, [$this, 'compareRanges']);

        return $ranges;
    }

    private function compareRanges(RangeInterface $firstRange, RangeInterface $secondRange): int
    {
        return $firstRange->getStart() <=> $secondRange->getStart();
    }

    private function mergeRanges(RangeInterface ...$ranges): RangeSetInterface
    {
        $mergedRanges = [];
        /** @var RangeInterface|null $rangeBuffer */
        $rangeBuffer = null;
        foreach ($this->createRangePicker($this->ranges, $ranges) as $pickedRange) {
            if (isset($rangeBuffer)) {
                if ($rangeBuffer->containsValue($pickedRange->getFinish())) {
                    continue;
                }
                if ($rangeBuffer->containsValue($pickedRange->getStart()) || $pickedRange->follows($rangeBuffer)) {
                    $rangeBuffer = $pickedRange->withStart($rangeBuffer->getStart());
                    continue;
                }
                $mergedRanges[] = $rangeBuffer;
            }
            $rangeBuffer = $pickedRange;
        }
        if (isset($rangeBuffer)) {
            $mergedRanges[] = $rangeBuffer;
        }

        return self::createUnsafe(...$mergedRanges);
    }

    public function xor(RangeSetInterface $rangeSet): RangeSetInterface
    {
        $xoredRanges = [];
        /** @var RangeInterface|null $rangeBuffer */
        $rangeBuffer = null;
        foreach ($this->createRangePicker($this->ranges, $rangeSet->getRanges()) as $pickedRange) {
            if (isset($rangeBuffer)) {
                if ($rangeBuffer->intersects($pickedRange)) {
                    $pickedRangeStart = $pickedRange->getStart();
                    if ($rangeBuffer->getStart() < $pickedRangeStart) {
                        $xoredRanges[] = $rangeBuffer->withFinish($pickedRangeStart - 1);
                        $rangeBuffer = $rangeBuffer->withStart($pickedRangeStart);
                    }

                    $pickedRangeFinish = $pickedRange->getFinish();
                    $rangeBufferFinish = $rangeBuffer->getFinish();
                    if ($rangeBufferFinish < $pickedRangeFinish) {
                        $rangeBuffer = $pickedRange->withStart($rangeBufferFinish + 1);
                    } elseif ($pickedRangeFinish < $rangeBufferFinish) {
                        $rangeBuffer = $rangeBuffer->withStart($pickedRangeFinish + 1);
                    } else {
                        $rangeBuffer = null;
                    }
                    continue;
                }
                if ($pickedRange->follows($rangeBuffer)) {
                    $rangeBuffer = $rangeBuffer->withFinish($pickedRange->getFinish());
                    continue;
                }
                $xoredRanges[] = $rangeBuffer;
            }
            $rangeBuffer = $pickedRange;
        }
        if (isset($rangeBuffer)) {
            $xoredRanges[] = $rangeBuffer;
        }

        return self::createUnsafe(...$xoredRanges);
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     */
    public function and(RangeSetInterface $rangeSet): RangeSetInterface
    {
        $andedRanges = [];
        /** @var RangeInterface|null $rangeBuffer */
        $rangeBuffer = null;
        foreach ($this->createRangePicker($this->ranges, $rangeSet->getRanges()) as $pickedRange) {
            if (isset($rangeBuffer)) {
                if (!$rangeBuffer->intersects($pickedRange)) {
                    continue;
                }
                $pickedRangeStart = $pickedRange->getStart();
                if ($rangeBuffer->getStart() < $pickedRangeStart) {
                    $rangeBuffer = $rangeBuffer->withStart($pickedRangeStart);
                }
                $pickedRangeFinish = $pickedRange->getFinish();
                if ($rangeBuffer->getFinish() > $pickedRangeFinish) {
                    $andedRanges[] = $rangeBuffer->withFinish($pickedRangeFinish);
                    $rangeBuffer = $rangeBuffer->withStart($pickedRangeFinish + 1);
                    continue;
                }
                $andedRanges[] = $rangeBuffer;
            }
            $rangeBuffer = $pickedRange;
        }

        return self::createUnsafe(...$andedRanges);
    }

    /**
     * @param RangeInterface[] ...$rangeLists
     * @return RangeInterface[]
     * @psalm-return iterable<int,RangeInterface>
     */
    private function createRangePicker(array ...$rangeLists): iterable
    {
        $rangeListKeys = array_keys($rangeLists);
        $indexes = array_fill_keys($rangeListKeys, 0);

        while (true) {
            $selectedRanges = array_map([$this, 'selectRanges'], $rangeLists, $indexes);
            $nextRange = array_reduce($selectedRanges, [$this, 'findRangeWithMinimalStart']);
            if (!isset($nextRange)) {
                break;
            }

            $nextRangeKey = array_search($nextRange, $selectedRanges);
            if (false === $nextRangeKey) {
                // @codeCoverageIgnoreStart
                throw new Exception\RangeNotPickedException();
                // @codeCoverageIgnoreEnd
            }
            $indexes[$nextRangeKey]++;

            yield $nextRange;
        }
    }

    /**
     * @param RangeInterface[] $ranges
     * @param int              $index
     * @return RangeInterface|null
     */
    private function selectRanges(array $ranges, int $index): ?RangeInterface
    {
        return $ranges[$index] ?? null;
    }

    /**
     * @param RangeInterface|null $previousRange
     * @param RangeInterface|null $range
     * @return RangeInterface|null
     */
    private function findRangeWithMinimalStart(?RangeInterface $previousRange, ?RangeInterface $range): ?RangeInterface
    {
        if (isset($previousRange)) {
            if (!isset($range)) {
                return $previousRange;
            }

            return $previousRange->getStart() < $range->getStart()
                ? $previousRange
                : $range;
        }

        return $range;
    }

    /**
     * {@inheritDoc}
     *
     * @return RangeInterface[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->ranges);
    }
}
