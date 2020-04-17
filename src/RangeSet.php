<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

use Generator;

use function array_map;
use function array_search;
use function count;
use function usort;

/**
 * Immutable set of integer ranges.
 *
 * @psalm-immutable
 */
final class RangeSet implements RangeSetInterface
{

    /**
     * @var RangeInterface[]
     */
    private $ranges = [];

    /**
     * Creates set of ranges that contain all values from given ranges.
     *
     * @param RangeInterface ...$ranges
     * @return RangeSetInterface
     * @psalm-pure
     */
    public static function create(RangeInterface ...$ranges): RangeSetInterface
    {
        $rangeSet = new self();

        return $rangeSet->withRanges(...$ranges);
    }

    /**
     * Provided ranges must be sorted, must not overlap and must not follow each other without gaps.
     * Warning: no checks are made! Use {@see RangeSet::createUnion()} to create set from arbitrary list of ranges.
     *
     * @param RangeInterface ...$ranges
     * @return RangeSetInterface
     * @psalm-pure
     */
    public static function createUnsafe(RangeInterface ...$ranges): RangeSetInterface
    {
        return new self(...$ranges);
    }

    /**
     * @param int[] ...$rangeDataList
     * @return RangeInterface[]
     * @psalm-pure
     */
    public static function importRanges(array ...$rangeDataList): array
    {
        /** @var RangeInterface[] $ranges */
        $ranges = array_map([self::class, 'importRange'], $rangeDataList);

        return $ranges;
    }

    /**
     * @param int[] $args
     * @return RangeInterface
     * @psalm-pure
     */
    private static function importRange(array $args): RangeInterface
    {
        return new Range(...$args);
    }

    private function __construct(RangeInterface ...$ranges)
    {
        $this->ranges = $ranges;
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function createUnion(RangeSetInterface $rangeSet): RangeSetInterface
    {
        return $this->mergeRanges(...$rangeSet->getRanges());
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeInterface ...$ranges
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface
    {
        return $this->mergeRanges(...$this->getSortedRanges(...$ranges));
    }

    /**
     * @param RangeInterface ...$ranges
     * @return RangeInterface[]
     * @psalm-return array<int,RangeInterface>
     * @psalm-pure
     */
    private function getSortedRanges(RangeInterface ...$ranges): array
    {
        usort($ranges, [$this, 'compareRanges']);

        return $ranges;
    }

    /**
     * @param RangeInterface $firstRange
     * @param RangeInterface $secondRange
     * @return int
     * @psalm-pure
     */
    private function compareRanges(RangeInterface $firstRange, RangeInterface $secondRange): int
    {
        return $firstRange->getStart() <=> $secondRange->getStart();
    }

    /**
     * @param RangeInterface ...$ranges
     * @return RangeSetInterface
     * @psalm-pure
     */
    private function mergeRanges(RangeInterface ...$ranges): RangeSetInterface
    {
        $resultRanges = [];
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
                $resultRanges[] = $rangeBuffer;
            }
            $rangeBuffer = $pickedRange;
        }
        if (isset($rangeBuffer)) {
            $resultRanges[] = $rangeBuffer;
        }

        return self::createUnsafe(...$resultRanges);
    }

    /**
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function createSymmetricDifference(RangeSetInterface $rangeSet): RangeSetInterface
    {
        $resultRanges = [];
        /** @var RangeInterface|null $rangeBuffer */
        $rangeBuffer = null;
        foreach ($this->createRangePicker($this->ranges, $rangeSet->getRanges()) as $pickedRange) {
            if (isset($rangeBuffer)) {
                if ($rangeBuffer->intersects($pickedRange)) {
                    $pickedRangeStart = $pickedRange->getStart();
                    if ($rangeBuffer->getStart() < $pickedRangeStart) {
                        $resultRanges[] = $rangeBuffer->withFinish($pickedRangeStart - 1);
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
                $resultRanges[] = $rangeBuffer;
            }
            $rangeBuffer = $pickedRange;
        }
        if (isset($rangeBuffer)) {
            $resultRanges[] = $rangeBuffer;
        }

        return self::createUnsafe(...$resultRanges);
    }

    /**
     * {@inheritDoc}
     *
     * @param RangeSetInterface $rangeSet
     * @return RangeSetInterface
     * @psalm-pure
     */
    public function createIntersection(RangeSetInterface $rangeSet): RangeSetInterface
    {
        $resultRanges = [];
        /** @var RangeInterface|null $rangeBuffer */
        $rangeBuffer = null;
        foreach ($this->createRangePicker($this->ranges, $rangeSet->getRanges()) as $pickedRange) {
            if (isset($rangeBuffer)) {
                if (!$rangeBuffer->intersects($pickedRange)) {
                    $rangeBuffer = $pickedRange;
                    continue;
                }
                $pickedRangeStart = $pickedRange->getStart();
                if ($rangeBuffer->getStart() < $pickedRangeStart) {
                    $rangeBuffer = $rangeBuffer->withStart($pickedRangeStart);
                }
                $pickedRangeFinish = $pickedRange->getFinish();
                if ($rangeBuffer->getFinish() > $pickedRangeFinish) {
                    $resultRanges[] = $rangeBuffer->withFinish($pickedRangeFinish);
                    $rangeBuffer = $rangeBuffer->withStart($pickedRangeFinish + 1);
                    continue;
                }
                $resultRanges[] = $rangeBuffer;
            }
            $rangeBuffer = $pickedRange;
        }

        return self::createUnsafe(...$resultRanges);
    }

    /**
     * @param RangeSetInterface $rangeSet
     * @return bool
     * @psalm-pure
     */
    public function equals(RangeSetInterface $rangeSet): bool
    {
        $ranges = $rangeSet->getRanges();
        if (count($this->ranges) != count($ranges)) {
            return false;
        }

        foreach ($this->ranges as $index => $range) {
            if (!$ranges[$index]->equals($range)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param RangeInterface[] ...$rangeLists
     * @return RangeInterface[]|Generator
     * @psalm-return Generator<int,RangeInterface>
     * @psalm-pure
     */
    private function createRangePicker(array ...$rangeLists): Generator
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
     * @psalm-pure
     */
    private function selectRanges(array $ranges, int $index): ?RangeInterface
    {
        return $ranges[$index] ?? null;
    }

    /**
     * @param RangeInterface|null $previousRange
     * @param RangeInterface|null $range
     * @return RangeInterface|null
     * @psalm-pure
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
     * @psalm-pure
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     * @psalm-pure
     */
    public function isEmpty(): bool
    {
        return empty($this->ranges);
    }
}
