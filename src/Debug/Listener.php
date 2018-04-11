<?php declare(strict_types=1);
namespace Behapi\Debug;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\EventDispatcher\Event\AfterTested;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;

use Behat\Gherkin\Node\TaggedNodeInterface;

use Behapi\Debug\Introspection\Adapter;

use Behapi\HttpHistory\Tuple as HttpTuple;
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

    /** @var Configuration */
    private $configuration;

    /* @var Adapter[] */
    private $adapters;

    /** @param Adapter[] $adapters Introspection adapters to use in this listener (sorted by priority) */
    public function __construct(Configuration $configuration, HttpHistory $history, array $adapters)
    {
        $this->history = $history;
        $this->adapters = $adapters;
        $this->configuration = $configuration;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return [
            ExampleTested::AFTER => 'debugAfter',
            ScenarioTested::AFTER => 'debugAfter',
        ];
    }

    public function debugAfter(GherkinNodeTested $event): void
    {
        if (!$event instanceof AfterTested) {
            return;
        }

        // no http tag... still no chocolates
        if (!$this->hasTag($event, 'http')) {
            return;
        }

        // debug only if tag is present (all debug) or only on test failures
        if ($this->hasTag($event, 'debug')) {
            foreach ($this->history as $http) {
                $this->debug($http);
            }

            return;
        }

        if (false === $this->configuration->getStatus()) {
            return;
        }

        $result = $event->getTestResult();

        if (TestResult::FAILED !== $result->getResultCode()) {
            return;
        }

        foreach ($this->history as $http) {
            $this->debug($http);
        }
    }

    private function debug(HttpTuple $http): void
    {
        $messages = [
            RequestInterface::class => $http->getRequest(),
            ResponseInterface::class => $http->getResponse(),
        ];

        /** @var MessageInterface $message */
        foreach ($messages as $interface => $message) {
            if (!$message instanceof $interface) {
                continue;
            }

            foreach ($this->adapters as $adapter) {
                if (!$adapter->supports($message)) {
                    continue;
                }

                $adapter->introspect($message);
                break;
            }
        }
    }

    private function hasTag(GherkinNodeTested $event, string $tag): bool
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
