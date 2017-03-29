<?php
namespace Behapi\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;

use Behapi\Extension\Tools\HttpHistory;

/**
 * Listener that cleans everything once a Scenario was finished
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Cleaner implements EventSubscriberInterface
{
    /** @var HttpHistory */
    private $history;

    public function __construct(HttpHistory $history)
    {
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return [
            OutlineTested::AFTER => ['clear', -99],
            ScenarioTested::AFTER => ['clear', -99]
        ];
    }

    /** Resets the current history of the current client */
    public function clear(): void
    {
        $this->history->reset();
    }
}

