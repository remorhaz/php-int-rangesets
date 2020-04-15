<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets;

interface RangeInterface
{

    public function getStart(): int;

    public function getFinish(): int;

    public function getLength(): int;

    public function equals(RangeInterface $range): bool;

    public function containsValue(int $value): bool;

    public function contains(RangeInterface $range): bool;

    public function intersects(RangeInterface $range): bool;

    public function follows(RangeInterface $range): bool;

    public function asRangeSet(): RangeSetInterface;

    public function withStart(int $value): RangeInterface;

    public function withFinish(int $value): RangeInterface;
}
