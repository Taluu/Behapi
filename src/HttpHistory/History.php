<?php declare(strict_types=1);
namespace Behapi\HttpHistory;

use IteratorAggregate;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Http\Client\Common\Plugin\Journal;

use Http\Client\Exception;
use Http\Client\Exception\HttpException;

use function end;
use function reset;
use function count;

final class History implements Journal, IteratorAggregate
{
    /** @var Tuple[] */
    private $tuples = [];

    /** {@inheritDoc} */
    public function addSuccess(RequestInterface $request, ResponseInterface $response)
    {
        $this->tuples[] = new Tuple($request, $response);
    }

    /** {@inheritDoc} */
    public function addFailure(RequestInterface $request, Exception $exception)
    {
        $response = $exception instanceof HttpException
            ? $exception->getResponse()
            : null;

        $this->tuples[] = new Tuple($request, $response);
    }

    public function getLastResponse(): ?ResponseInterface
    {
        if (1 > count($this->tuples)) {
            return null;
        }

        /** @var Tuple $tuple */
        $tuple = end($this->tuples);
        reset($this->tuples);

        return $tuple->getResponse();
    }

    /** @return iterable<Tuple> */
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
