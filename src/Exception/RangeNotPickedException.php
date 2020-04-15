<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Exception;

use OutOfRangeException;
use Throwable;

final class RangeNotPickedException extends OutOfRangeException implements ExceptionInterface
{

    public function __construct(Throwable $previous = null)
    {
        parent::__construct("Failed to pick range from query", 0, $previous);
    }
}
