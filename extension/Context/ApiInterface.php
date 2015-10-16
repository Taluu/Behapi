<?php
namespace Wisembly\Behat\Extension\Context;

use RuntimeException;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\History as GuzzleHistory;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;

/**
 * Base context interface that all api contexts must implement
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
interface ApiInterface
{
    /**
     * Get the latest response
     *
     * @return GuzzleResponse
     * @throws RuntimeException No request sent, no response received
     */
    public function getResponse();

    /** Setup this context */
    public function initializeApi(GuzzleClient $client, GuzzleHistory $history);
}

