<?php
namespace Behapi\Extension\Context;

use RuntimeException;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\History as GuzzleHistory;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;

trait ApiTrait
{
    /** @var GuzzleClient */
    private $client = null;

    /** @var GuzzleHistory */
    private $history = null;

    /** {@inheritDoc} */
    public function initializeApi(GuzzleClient $client, GuzzleHistory $history)
    {
        $this->client = $client;
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public function getResponse()
    {
        $history = $this->getHistory();

        if (0 === count($history)) {
            throw new RuntimeException('No request were sent');
        }

        $response = $history->getLastResponse();

        if (!$response instanceof GuzzleResponse) {
            throw new RuntimeException('No response');
        }

        return $this->response = $response;
    }

    /**
     * Get the guzzle http client
     *
     * @return GuzzleHttp
     */
    private function getClient()
    {
        if (null === $this->client) {
            throw new RuntimeException('The client was not initialized within this context');
        }

        return $this->client;
    }

    /**
     * Get the Guzzle History
     *
     * @return GuzzleHistory
     */
    private function getHistory()
    {
        if (null === $this->history) {
            throw new RuntimeException('The history was not initialized within this context');
        }

        return $this->history;
    }
}

