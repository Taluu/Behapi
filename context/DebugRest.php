<?php
namespace Behapi\Context;

use RuntimeException;

use Behat\Behat\Context\Context;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Behat\Hook\Scope\AfterScenarioScope;

use GuzzleHttp\Message\RequestInterface as GuzzleRequest;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;

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

    private function debug()
    {
        if (null === $this->debug) {
            return;
        }

        $history = $this->getHistory();

        if (0 === count($history)) {
            return;
        }

        $request = $history->getLastRequest();
        $response = $history->getLastResponse();

        foreach ($this->getDebug($request, $response) as $key => $value) {
            echo "\033[36m| \033[1m$key : \033[0;36m$value\033[0m\n";
        }

        echo "\n";
        echo (string) $response->getBody();
    }

    protected function getDebug(GuzzleRequest $request, ?GuzzleResponse $response): iterable
    {
        $debug = [
            'Request' => "{$request->getMethod()} {$request->getUrl()}",
            'Request Content-Type' => $request->getHeader('Content-Type')
        ];

        if ($response instanceof GuzzleResponse) {
            $debug['Response status-code'] = $response->getStatusCode();
            $debug['Response content-type'] = $response->getHeader('Content-Type');
        }

        return $debug;
    }
}

