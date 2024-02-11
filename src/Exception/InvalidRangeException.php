<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Exception;

use LogicException;
use Throwable;

final class InvalidRangeException extends LogicException implements ExceptionInterface
{
    public function __construct(
        private readonly int $start,
        private readonly int $finish,
        ?Throwable $previous = null,
    ) {
        parent::__construct("Invalid range: [$this->start, $this->finish]", previous: $previous);
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
