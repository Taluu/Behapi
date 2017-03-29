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

        // force debug ?
        if ($this->hasTag($event, 'debug')) {
            $this->debug();
            return;
        }

        if (false === $this->debug->getStatus()) {
            return;
        }

        if (TestResult::FAILED !== $event->getTestResult()->getResultCode()) {
            return;
        }

        $this->debug();
    }

    private function debug(): void
    {
        $request = $this->history->getLastRequest();
        $response = $this->history->getLastResponse();

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
