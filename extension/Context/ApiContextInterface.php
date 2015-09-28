<?php

namespace features\bootstrap\Extension\Context;

use Guzzle\Http\Client as GuzzleHttp,
    Guzzle\Http\Message\Response as GuzzleResponse,

    Guzzle\Plugin\History\HistoryPlugin as GuzzleHistory;

use Wisembly\CoreBundle\Domain\Bag;

use features\bootstrap\Extension\Guzzle\AuthenticationPlugin;

/**
 * Base context interface that all api contexts must implement
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
interface ApiContextInterface extends WizContextInterface
{
    const DEFAULT_VERSION = 4;

    /** Get the current associated token (string) */
    public function getAuthenticationPlugin();

    /** Set the current api version used */
    public function setApiVersion($version);

    /** Setup Guzzle for this context */
    public function setupGuzzle(GuzzleHttp $client, GuzzleHistory $history, AuthenticationPlugin $plugin);

    /**
     * Send a request
     *
     * @param string $method     HTTP Verb used for this request
     * @param string $url        URL to send the request to
     * @param array  $parameters Parameters to send with the request
     * @param string $body       Content of the request to send
     * @param array  $server     Headers to send with the request
     * @param array  $options    Options to pass to the GuzzleRequest
     */
    public function sendRequest($method, $url, $parameters = [], $body = null, array $server = [], array $options = []);

    /**
     * Gets the actual response
     *
     * @return GuzzleResponse
     */
    public function getResponse();
}

