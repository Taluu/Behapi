<?php
namespace Behapi\Extension\Context;

use RuntimeException;

use Psr\Http\Message\ResponseInterface;

use Http\Client\HttpClient;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Behapi\Extension\Tools\HttpHistory;

trait ApiTrait
{
    /** @var HttpClient */
    private $client;

    /** @var StreamFactory */
    private $streamFactory;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var HttpHistory */
    private $history;

    public function getResponse(): ResponseInterface
    {
        $response = $this->history->getLastResponse();

        if (!$response instanceof ResponseInterface) {
            throw new RuntimeException('No response');
        }

        return $response;
    }
}
