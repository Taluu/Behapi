<?php declare(strict_types=1);
namespace Behapi\HttpHistory;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Testwork\EventDispatcher\Event\BeforeTested;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;

/**
 * Starts a new "section" in the HttpHistory object when a scenario-like is
 * starting, and clean everything once it's done
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class Listener implements EventSubscriberInterface
{
    /** @var History */
    private $history;

    /** @var bool */
    private $inBackground = false;

    public function __construct(History $history)
    {
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return [
            ExampleTested::BEFORE => 'start',
            ScenarioTested::BEFORE => 'start',
            BackgroundTested::BEFORE => 'start',

            ExampleTested::AFTER => ['clear', -99],
            ScenarioTested::AFTER => ['clear', -99],
        ];
    }

    public function start(BeforeTested $event, string $eventName): void
    {
        if ($this->inBackground === true) {
            return;
        }

        if (BackgroundTested::BEFORE === $eventName) {
            $this->inBackground = true;
        }

        $this->history->start();
    }

    /** Resets the current history of the current client */
    public function clear(): void
    {
        $this->history->reset();
        $this->inBackground = false;
    }
}
