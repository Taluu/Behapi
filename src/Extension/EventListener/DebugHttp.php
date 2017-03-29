<?php
namespace Behapi\Extension\EventListener;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\EventDispatcher\Event\AfterTested;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;

use Behat\Gherkin\Node\TaggedNodeInterface;

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Tools\HttpHistory;

/**
 * Debug http
 *
 * Allows to debug a scenario, or, if the debug is activated, to print
 * a message if a scenario failed for http requests.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class DebugHttp implements EventSubscriberInterface
{
    /** @var HttpHistory */
    private $history;

    /** @var Debug */
    private $debug;

    public function __construct(Debug $debug, HttpHistory $history)
    {
        $this->debug = $debug;
        $this->history = $history;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return [
            OutlineTested::AFTER => 'debugAfter',
            ScenarioTested::AFTER => 'debugAfter',
            BackgroundTested::AFTER => 'debugAfter',
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

        $history = $this->history;

        // debug only if tag is present (all debug) or only on test failures
        if (!$this->hasTag($event, 'debug')) {
            $history = $this->history;

            if (false === $this->debug->getStatus()) {
                return;
            }

            $result = $event->getTestResult();

            if (TestResult::FAILED !== $result->getResultCode()) {
                return;
            }

            if (is_iterable($result)) {
                $history = [];
                $values = iterator_to_array($this->history);

                foreach ($result as $key => $testResult) {
                    if (TestResult::FAILED !== $testResult->getResultCode()) {
                        continue;
                    }

                    $history[] = $values[$key];
                }
            }
        }

        $this->debug($history);
    }

    private function debug(iterable $historyTuples): void
    {
        foreach ($historyTuples as $tuple) {
            $this->doDebug($tuple[0], $tuple[1]);
        }
    }

    private function doDebug(?RequestInterface $request, ?ResponseInterface $response): void
    {
        if (!$request instanceof RequestInterface) {
            return;
        }

        $debug = $this->getDebug($request, $response);

        foreach ($debug as $key => $value) {
            echo "\033[36m| \033[1m$key : \033[0;36m$value\033[0m\n";
        }

        if ($response instanceof ResponseInterface) {
            echo "\n";
            echo (string) $response->getBody();
        }

        echo "\n";
    }

    /**
     * Get the interesting header informations to display
     *
     * @return iterable
     */
    protected function getDebug(RequestInterface $request, ?ResponseInterface $response): iterable
    {
        $debug = [
            'Request' => "{$request->getMethod()} {$request->getUri()}",
            'Request Content-Type' => $request->getHeaderLine('Content-Type'),
        ];

        if ($response instanceof ResponseInterface) {
            $debug['Response status'] = "{$response->getStatusCode()} {$response->getReasonPhrase()}";
            $debug['Response Content-Type'] = $response->getHeaderLine('Content-Type');
        }

        return $debug;
    }

    private function hasTag(GherkinNodeTested $event, string $tag): bool
    {
        $node = $event->getNode();

        // no tags, no chocolates
        if (!$node instanceof TaggedNodeInterface) {
            return false;
        }

        if ($node->hasTag($tag)) {
            return true;
        }

        if (!method_exists($event, 'getFeature')) {
            return false;
        }

        $feature = $event->getFeature();

        return $feature->hasTag($tag);
    }
}
