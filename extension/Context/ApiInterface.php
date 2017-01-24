<?php
namespace Behapi\Extension\Context;

use RuntimeException;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\History as GuzzleHistory;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;

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
     * @return GuzzleResponse
     * @throws RuntimeException No request sent, no response received
     */
    public function getResponse(): GuzzleResponse;

    /** Setup this context */
    public function initializeApi(GuzzleClient $client, GuzzleHistory $history): void;
}

