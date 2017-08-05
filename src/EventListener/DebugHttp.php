<?php
namespace Behapi\EventListener;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\EventDispatcher\Event\AfterTested;

use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\GherkinNodeTested;

use Behat\Gherkin\Node\TaggedNodeInterface;

use Behapi\Tools\Debug;
use Behapi\Tools\HttpHistory as History;

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
    // 1 - key
    // 2 - value
    const TEMPLATE = "\033[36m| \033[1m%s : \033[0;36m%s\033[0m\n";

    /** @var History */
    private $history;

    /** @var Debug */
    private $debug;

    public function __construct(Debug $debug, History $history)
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

        // debug only if tag is present (all debug) or only on test failures
        if ($this->hasTag($event, 'debug')) {
            $tuples = current($this->history);

            foreach ($tuples as $tuple) {
                foreach ($tuple as list($request, $response)) {
                    $this->debug($request, $response);
                }
            }

            return;
        }

        if (false === $this->debug->getStatus()) {
            return;
        }

        $result = $event->getTestResult();

        if (TestResult::FAILED !== $result->getResultCode()) {
            return;
        }

        // all ->getTestResult() returns actually TestResults even for simple
        // scenarios. So we have to ensure that if we are not testing against
        // OutlineTested, we need to wrap the result in an array
        if ($result instanceof TestResults && !$event instanceof OutlineTested) {
            $result = [$result];
        }

        $values = iterator_to_array($this->history);
        $key = -1;

        foreach ($result as $testResult) {
            ++$key;

            if (TestResult::FAILED !== $testResult->getResultCode()) {
                continue;
            }

            // no history created
            if (!isset($values[$key])) {
                continue;
            }

            foreach ($values[$key] as list($request, $response)) {
                $this->debug($request, $response);
            }
        }
    }

    private function debug(?RequestInterface $request, ?ResponseInterface $response): void
    {
        if (!$request instanceof RequestInterface) {
            return;
        }

        printf(self::TEMPLATE, 'Request', "{$request->getMethod()} {$request->getUri()}");

        foreach ($this->debug->getRequestHeaders() as $header) {
            printf(self::TEMPLATE, "Request {$header}", $request->getHeaderLine($header));
        }

        echo "\n";

        if (!$response instanceof ResponseInterface) {
            return;
        }

        printf(self::TEMPLATE, 'Response status', "{$response->getStatusCode()} {$response->getReasonPhrase()}");

        foreach ($this->debug->getResponseHeaders() as $header) {
            printf(self::TEMPLATE, "Response {$header}", $response->getHeaderLine($header));
        }

        echo "\n";
        echo (string) $response->getBody();
        echo "\n";
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
