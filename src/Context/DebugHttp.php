<?php
namespace Behapi\Context;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Behat\Testwork\Tester\Result\TestResult;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Tools\LastHistory;

/**
 * Debug http
 *
 * Allows to debug a scenario, or, if the debug is activated, to print
 * a message if a scenario failed for http requests.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class DebugRest implements Context
{
    /** @var LastHistory */
    private $history;

    /** @var Debug */
    private $debug;

    public function __construct(Debug $debug, LastHistory $history)
    {
        $this->debug = $debug;
        $this->history = $history;
    }

    /** @AfterScenario @http */
    public function debugAfter(AfterScenarioScope $scope)
    {
        if (null === $this->debug) {
            return;
        }

        if ($scope->getScenario()->hasTag('debug')) {
            $this->debug();
            return;
        }

        if (false === $this->debug->getStatus()) {
            return;
        }

        if (TestResult::FAILED !== $scope->getTestResult()->getResultCode()) {
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

        echo "\n";
        echo (string) $response->getBody();
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
}

