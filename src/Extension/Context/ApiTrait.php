<?php
namespace Behapi\Extension\Context;

use RuntimeException;

use Psr\Http\Message\ResponseInterface;

use Http\Client\HttpClient;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Behapi\Extension\Tools\LastHistory;

trait ApiTrait
{
    /** @var HttpClient */
    private $client;

    /** @var StreamFactory */
    private $streamFactory;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var LastHistory */
    private $history;

    /** {@inheritDoc} */
    public function getResponse(): ResponseInterface
    {
        $history = $this->getHistory();
        $response = $history->getLastResponse();

        if (!$response instanceof ResponseInterface) {
            throw new RuntimeException('No response');
        }

        return $response;
    }
}

