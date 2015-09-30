<?php

namespace features\bootstrap\Extension\Context\Initializer;

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;

use Guzzle\Http\Client as GuzzleHttp;
use Guzzle\Plugin\History\HistoryPlugin as GuzzleHistory;

use features\bootstrap\Extension\Guzzle\AuthenticationPlugin;
use features\bootstrap\Extension\Context\ApiContextInterface;

/**
 * Initializes all api contexts
 *
 * Give them access to the token plugin
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class ApiInitializer implements InitializerInterface
{
    /** @var GuzzleHttp */
    private $client;

    /** @var GuzzleHistory */
    private $history;

    /** @var AuthenticationPlugin */
    private $plugin;

    public function __construct(GuzzleHttp $client, GuzzleHistory $history, AuthenticationPlugin $plugin)
    {
        $this->client  = $client;
        $this->plugin  = $plugin;
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public function supports(ContextInterface $context)
    {
        return $context instanceof ApiContextInterface;
    }

    /** {@inheritDoc} */
    public function initialize(ContextInterface $context)
    {
        $context->setupGuzzle($this->client, $this->history, $this->plugin);
    }
}

