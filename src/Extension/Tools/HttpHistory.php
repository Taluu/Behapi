<?php
namespace Behapi\Extension\Tools;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Http\Client\Common\Plugin\Journal;

use Http\Client\Exception;
use Http\Client\Exception\HttpException;

class HttpHistory implements Journal
{
    /** @var MessageInterface[][] Array of tuples of RequestInterface and ?ResponseInterface */
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

    public function getLastRequest(): ?RequestInterface
    {
        if (1 < count($this->tuples)) {
            return null;
        }

        $tuple = end($this->tuples);
        reset($this->tuples);

        return $tuple[0];
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

    public function reset(): void
    {
        $this->tuples = [];
    }
}
