<?php declare(strict_types=1);
namespace Behapi\HttpHistory;

use Throwable;
use RuntimeException;

final class NoResponse extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('No response available', 0, $previous);
    }
}
