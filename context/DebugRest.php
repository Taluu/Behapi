<?php
namespace Behapi\Context;

use RuntimeException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Behat\Behat\Context\Context;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

use Behapi\Extension\Context\ApiTrait;
use Behapi\Extension\Context\DebugTrait;
use Behapi\Extension\Context\ApiInterface;
use Behapi\Extension\Context\DebugInterface;

/**
 * Debug rest
 *
 * Allows to debug a scenario, or, if the debug is activated, to print
 * a message if a scenario failed for rest requests.
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class DebugRest implements Context, ApiInterface, DebugInterface
{
    use ApiTrait;
    use DebugTrait;

    /** @AfterScenario @api */
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
        $history = $this->getHistory();

        $request = $history->getLastRequest();
        $response = $history->getLastResponse();

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

