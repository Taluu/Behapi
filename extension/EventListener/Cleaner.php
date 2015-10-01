<?php
namespace Wisembly\Behat\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use GuzzleHttp\Subscriber\History as HistorySubscriber;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;

use Wisembly\CoreBundle\Domain\Bag;

/**
 * Listener that cleans everything once a Scenario was finished
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class Cleaner implements EventSubscriberInterface
{
    /** @var GuzzleHistory */
    private $history;

    /** @var Bag */
    private $bag;

    public function __construct(HistorySubscriber $history, Bag $bag)
    {
        $this->bag = $bag;
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
    public function clear()
    {
        $this->bag->reset();
        $this->history->clear();
    }
}

