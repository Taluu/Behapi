<?php declare(strict_types=1);
namespace Behapi\HttpHistory;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Tuple
{
    /** @var RequestInterface */
    private $request;

    /** @var ?ResponseInterface */
    private $response;

    public function __construct(RequestInterface $request, ?ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
