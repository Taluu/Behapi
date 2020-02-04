<?php declare(strict_types=1);
namespace Behapi\HttpHistory;

use Generator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;

use Http\Client\Common\Plugin\Journal;

use Http\Client\Exception\HttpException;

use function end;
use function reset;
use function count;

final class History implements Journal
{
    /** @var list<Tuple> */
    private $tuples = [];

    public function addSuccess(RequestInterface $request, ResponseInterface $response): void
    {
        $this->tuples[] = new Tuple($request, $response);
    }

    public function addFailure(RequestInterface $request, ClientExceptionInterface $exception): void
    {
        $response = $exception instanceof HttpException
            ? $exception->getResponse()
            : null
        ;

        $this->tuples[] = new Tuple($request, $response);
    }

    public function getLastResponse(): ResponseInterface
    {
        if (1 > count($this->tuples)) {
            throw new NoResponse;
        }

        $tuple = end($this->tuples);
        reset($this->tuples);

        $response = $tuple->getResponse();

        if (null === $response) {
            throw new NoResponse;
        }

        return $response;
    }

    public function getTuples(): Generator
    {
        yield from $this->tuples;

        return count($this->tuples);
    }

    public function reset(): void
    {
        $this->tuples = [];
    }
}
