<?php declare(strict_types=1);
namespace Behapi\HttpHistory;

use Iterator;
use ArrayIterator;
use IteratorAggregate;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Http\Client\Common\Plugin\Journal;

use Http\Client\Exception;
use Http\Client\Exception\HttpException;

use function end;
use function key;
use function reset;
use function count;

final class History implements Journal, IteratorAggregate
{
    /** @var ?MessageInterface[][] */
    private $tuples = [];

    /** {@inheritDoc} */
    public function addSuccess(RequestInterface $request, ResponseInterface $response)
    {
        $this->tuples[] = [$request, $response];
    }

    /** {@inheritDoc} */
    public function addFailure(RequestInterface $request, Exception $exception)
    {
        $tuple = [$request, null];

        if ($exception instanceof HttpException) {
            $tuple[1] = $exception->getResponse();
        }

        $this->tuples[] = $tuple;
    }

    public function getLastResponse(): ?ResponseInterface
    {
        if (1 > count($this->tuples)) {
            return null;
        }

        $tuple = end($this->tuples);
        reset($this->tuples);

        return $tuple[1];
    }

    public function getIterator(): iterable
    {
        yield from $this->tuples;

        return count($this->tuples);
    }

    public function reset(): void
    {
        $this->tuples = [];
    }
}
