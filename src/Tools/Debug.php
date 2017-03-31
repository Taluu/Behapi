<?php
namespace Behapi\Extension\Tools;

/**
 * Object containing the debug configuration (status, headers)
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Debug
{
    /** @var bool */
    private $status = false;

    /** @var string[] Request headers to print when debugging */
    private $requestHeaders = [];

    /** @var string[] Response headers to print when debugging */
    private $responseHeaders = [];

    public function __construct(array $requestHeaders, array $responseHeaders)
    {
        $this->requestHeaders = $requestHeaders;
        $this->responseHeaders = $responseHeaders;
    }

    public function setStatus(bool $status)
    {
        $this->status = $status;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }
}
