<?php
namespace Behapi\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;

use Behapi\Extension\Tools\LastHistory;

/**
 * Listener that cleans everything once a Scenario was finished
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Cleaner implements EventSubscriberInterface
{
    /** @var LastHistory */
    private $history;

    public function __construct(LastHistory $history)
    {
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return [
            OutlineTested::AFTER => 'clear',
            ScenarioTested::AFTER => 'clear'
        ];
    }

    /** Resets the current history of the current client */
    public function clear(): void
    {
        $this->history->reset();
    }
}

