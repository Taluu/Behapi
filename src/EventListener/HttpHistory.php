<?php
namespace Behapi\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;

use Behapi\Tools\HttpHistory as History;

/**
 * Starts a new "section" in the HttpHistory object when a scenario-like is
 * starting, and clean everything once it's done
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class HttpHistory implements EventSubscriberInterface
{
    /** @var History */
    private $history;

    public function __construct(History $history)
    {
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return [
            OutlineTested::BEFORE => 'start',
            ScenarioTested::BEFORE => 'start',
            BackgroundTested::BEFORE => 'start',

            OutlineTested::AFTER => ['clear', -99],
            ScenarioTested::AFTER => ['clear', -99],
            BackgroundTested::AFTER => ['clear', -99]
        ];
    }

    public function start(): void
    {
        $this->history->start();
    }

    /** Resets the current history of the current client */
    public function clear(): void
    {
        $this->history->reset();
    }
}
