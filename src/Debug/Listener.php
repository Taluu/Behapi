<?php declare(strict_types=1);
namespace Behapi\Debug;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\EventDispatcher\Event\AfterTested;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\ScenarioLikeTested;

use Behat\Gherkin\Node\TaggedNodeInterface;

use Behapi\Debug\Introspection\Adapter;

use Behapi\HttpHistory\History as HttpHistory;

use function method_exists;

/**
 * Debug http
 *
 * Allows to debug a scenario, or, if the debug is activated, to print
 * a message if a scenario failed for http requests.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class Listener implements EventSubscriberInterface
{
    /** @var HttpHistory */
    private $history;

    /** @var Status */
    private $status;

    /** @var Adapter[] */
    private $adapters;

    /** @param Adapter[] $adapters Introspection adapters to use in this listener (sorted by priority) */
    public function __construct(Status $status, HttpHistory $history, array $adapters)
    {
        $this->history = $history;
        $this->adapters = $adapters;
        $this->status = $status;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExampleTested::AFTER => 'debugAfter',
            ScenarioTested::AFTER => 'debugAfter',
        ];
    }

    public function debugAfter(ScenarioLikeTested $event): void
    {
        if (!$event instanceof AfterTested) {
            return;
        }

        // no http tag... no chocolates
        if (!$this->hasTag($event, 'http')) {
            return;
        }

        $result = $event->getTestResult();

        // debug only if tag is present (all debug) or only on test failures
        if (
            !$this->hasTag($event, 'debug')
            && (false === $this->status->isEnabled() || TestResult::FAILED !== $result->getResultCode())
        ) {
            return;
        }

        foreach ($this->history->getTuples() as $http) {
            $this->debug($http->getRequest());

            $response = $http->getResponse();

            if ($response instanceof ResponseInterface) {
                $this->debug($response);
            }
        }
    }

    private function debug(MessageInterface $message): void
    {
        foreach ($this->adapters as $adapter) {
            if (!$adapter->supports($message)) {
                continue;
            }

            $adapter->introspect($message);
            break;
        }
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress UndefinedDocblockClass
     */
    private function hasTag(ScenarioLikeTested $event, string $tag): bool
    {
        $node = $event->getNode();

        if ($node instanceof TaggedNodeInterface && $node->hasTag($tag)) {
            return true;
        }

        if (!method_exists($event, 'getFeature')) {
            return false;
        }

        $feature = $event->getFeature();

        return $feature->hasTag($tag);
    }
}
