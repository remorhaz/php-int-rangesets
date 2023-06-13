<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Exception;

use LogicException;
use Throwable;

final class InvalidRangeException extends LogicException implements ExceptionInterface
{
    public function __construct(
        private int $start,
        private int $finish,
        ?Throwable $previous = null,
    ) {
        parent::__construct("Invalid range: [$this->start, $this->finish]", 0, $previous);
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getFinish(): int
    {
        return $this->finish;
    }
}
