<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

use function array_map;
use function array_search;
use function array_values;
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
     * @var list<RangeInterface>
     */
    private array $ranges;

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
     * @psalm-pure
     */
    public static function createUnsafe(RangeInterface ...$ranges): RangeSetInterface
    {
        return new self(...$ranges);
    }

    /**
     * @param array{int, int|null} ...$rangeDataList
     * @return list<RangeInterface>
     * @psalm-pure
     */
    public static function importRanges(array ...$rangeDataList): array
    {
        return array_map(
            static fn (array $args): RangeInterface => new Range(/** @scrutinizer ignore-type */ ...$args),
            array_values($rangeDataList),
        );
    }

    private function __construct(RangeInterface ...$ranges)
    {
        $this->ranges = array_values($ranges);
    }

    /**
     * {@inheritDoc}
     */
    public function createUnion(RangeSetInterface $rangeSet): RangeSetInterface
    {
        return $this->mergeRanges(...$rangeSet->getRanges());
    }

    /**
     * {@inheritDoc}
     */
    public function withRanges(RangeInterface ...$ranges): RangeSetInterface
    {
        return $this->mergeRanges(...$this->getSortedRanges(...$ranges));
    }

    /**
     * @param RangeInterface ...$ranges
     * @return list<RangeInterface>
     */
    private function getSortedRanges(RangeInterface ...$ranges): array
    {
        usort(
            $ranges,
            fn (RangeInterface $firstRange, RangeInterface $secondRange): int =>
                $firstRange->getStart() <=> $secondRange->getStart(),
        );

        return $ranges;
    }

    private function mergeRanges(RangeInterface ...$ranges): RangeSetInterface
    {
        $resultRanges = [];
        /** @var RangeInterface|null $rangeBuffer */
        $rangeBuffer = null;
        foreach ($this->createRangePicker($this->ranges, array_values($ranges)) as $pickedRange) {
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
     * @param list<RangeInterface> ...$rangeLists
     * @return iterable<int, RangeInterface>
     */
    private function createRangePicker(array ...$rangeLists): iterable
    {
        $rangeListKeys = array_keys($rangeLists);
        $indexes = array_fill_keys($rangeListKeys, 0);

        while (true) {
            $selectedRanges = array_map(
                static fn (array $ranges, int $index): ?RangeInterface => $ranges[$index] ?? null,
                $rangeLists,
                $indexes,
            );
            $nextRange = array_reduce(
                $selectedRanges,
                static fn (?RangeInterface $previousRange, ?RangeInterface $range): ?RangeInterface =>
                    isset($previousRange, $range) && $previousRange->getStart() < $range->getStart()
                        ? $previousRange
                        : $range ?? $previousRange,
            );
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
     * {@inheritDoc}
     *
     * @return list<RangeInterface>
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->ranges);
    }
}
