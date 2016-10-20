<?php
namespace Wisembly\Behat\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use GuzzleHttp\Subscriber\History as HistorySubscriber;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;

use Wisembly\Behat\Extension\Tools\Bag;

/**
 * Listener that cleans everything once a Scenario was finished
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class Cleaner implements EventSubscriberInterface
{
    /** @var GuzzleHistory */
    private $history;

    /** @var Bag[] */
    private $bags = [];

    public function __construct(HistorySubscriber $history)
    {
        $this->history = $history;
    }

    public function addBag(Bag $bag)
    {
        $this->bags[] = $bag;
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
        $this->history->clear();

        foreach ($this->bags as $bag) {
            $bag->reset();
        }
    }
}

