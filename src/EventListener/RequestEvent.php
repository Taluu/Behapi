<?php declare(strict_types=1);
namespace Behapi\EventListener;

use Psr\Http\Message\RequestInterface;

use Symfony\Component\EventDispatcher\Event;

final class RequestEvent extends Event
{
    /** @var RequestInterface */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }
}
