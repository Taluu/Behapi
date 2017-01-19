<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Subscriber\History as GuzzleHistory;

use Behapi\Extension\Context\ApiInterface;

/**
 * Initializes all api contexts
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Api implements ContextInitializer
{
    /** @var GuzzleHttp */
    private $client;

    /** @var GuzzleHistory */
    private $history;

    public function __construct(GuzzleHttp $client, GuzzleHistory $history)
    {
        $this->client = $client;
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof ApiInterface) {
            return;
        }

        $context->initializeApi($this->client, $this->history);
    }
}

