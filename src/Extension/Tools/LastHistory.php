<?php
namespace Behapi\Extension\Tools;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Http\Client\Common\Plugin\Journal;

use Http\Client\Exception;
use Http\Client\Exception\HttpException;

class LastHistory implements Journal
{
    /** @var RequestInterface */
    private $lastRequest = null;

    /** @var ResponseInterface */
    private $lastResponse = null;

    /** {@inheritDoc} */
    public function addSuccess(RequestInterface $request, ResponseInterface $response)
    {
        $this->lastRequest = $request;
        $this->lastResponse = $response;
    }

    /** {@inheritDoc} */
    public function addFailure(RequestInterface $request, Exception $exception)
    {
        $this->lastRequest = $request;
        $this->lastResponse = null;

        if ($exception instanceof HttpException) {
            $this->lastResponse = $exception->getResponse();
        }
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->lastRequest;
    }

    public function getLastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    public function reset(): void
    {
        $this->lastRequest = null;
        $this->lastResponse = null;
    }
}

