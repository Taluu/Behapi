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
    public function initializeApi(HttpClient $client, StreamFactory $streamFactory, MessageFactory $messageFactory, LastHistory $history): void
    {
        $this->client = $client;
        $this->streamFactory = $streamFactory;
        $this->messageFactory = $messageFactory;

        $this->history = $history;
    }

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

    /**
     * Get the http client
     *
     * @return HttpClient
     */
    private function getClient(): HttpClient
    {
        if (null === $this->client) {
            throw new RuntimeException('The client was not initialized within this context');
        }

        return $this->client;
    }

    /**
     * Get the http message factory
     *
     * @return MessageFactory
     */
    private function getMessageFactory(): MessageFactory
    {
        if (null === $this->messageFactory) {
            throw new RuntimeException('The message factory was not initialized within this context');
        }

        return $this->messageFactory;
    }

    /**
     * Get the http stream factory
     *
     * @return StreamFactory
     */
    private function getStreamFactory(): StreamFactory
    {
        if (null === $this->streamFactory) {
            throw new RuntimeException('The stream factory was not initialized within this context');
        }

        return $this->streamFactory;
    }

    /**
     * Get the History
     *
     * @return LastHistory
     */
    private function getHistory(): LastHistory
    {
        if (null === $this->history) {
            throw new RuntimeException('The history was not initialized within this context');
        }

        return $this->history;
    }
}

