<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Http\Client\HttpClient;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Behapi\Extension\Tools\LastHistory;
use Behapi\Extension\Context\ApiInterface;

/**
 * Initializes all api contexts
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Api implements ContextInitializer
{
    /** @var HttpClient */
    private $client;

    /** @var StreamFactory */
    private $streamFactory;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var LastHistory */
    private $history;

    public function __construct(HttpClient $client, StreamFactory $streamFactory, MessageFactory $messageFactory, LastHistory $history)
    {
        $this->client = $client;
        $this->streamFactory = $streamFactory;
        $this->messageFactory = $messageFactory;

        $this->history = $history;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof ApiInterface) {
            return;
        }

        $context->initializeApi($this->client, $this->streamFactory, $this->messageFactory, $this->history);
    }
}

