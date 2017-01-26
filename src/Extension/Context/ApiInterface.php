<?php
namespace Behapi\Extension\Context;

use RuntimeException;

use Psr\Http\Message\ResponseInterface;

use Http\Client\HttpClient;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Behapi\Extension\Tools\LastHistory;

/**
 * Base context interface that all api contexts must implement
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
interface ApiInterface
{
    /**
     * Get the latest response
     *
     * @return ResponseInterface
     * @throws RuntimeException No request sent, no response received
     */
    public function getResponse(): ResponseInterface;
}

