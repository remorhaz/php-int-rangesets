<?php

declare(strict_types=1);

namespace Remorhaz\IntRangeSets\Exception;

use LogicException;
use Throwable;

final class InvalidRangeException extends LogicException implements ExceptionInterface
{

    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $finish;

    public function __construct(int $start, int $finish, Throwable $previous = null)
    {
        $this->start = $start;
        $this->finish = $finish;
        parent::__construct("Invalid range: [{$this->start}, {$this->finish}]", 0, $previous);
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
