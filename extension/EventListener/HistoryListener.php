<?php

namespace features\bootstrap\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Guzzle\Plugin\History\HistoryPlugin as GuzzleHistory;

/**
 * Listener that resets the history after a scenario is finished
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class HistoryListener implements EventSubscriberInterface
{
    /** @var GuzzleHistory */
    private $history;

    public function __construct(GuzzleHistory $history)
    {
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return ['afterScenario'       => 'resetHistory',
                'afterOutlineExample' => 'resetHistory'];
    }

    /** Resets the current history of the current client */
    public function resetHistory()
    {
        $this->history->clear();
    }
}

