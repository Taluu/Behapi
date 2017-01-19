<?php
namespace Behapi\Extension\Context;

use Behapi\Extension\Tools\Debug;

trait WizTrait
{
    /** @var string environment which under the behat test suite is run */
    private $environment = 'dev';

    /** @var Debug */
    private $debug;

    public function initializeWiz($environment, Debug $debug)
    {
        $this->debug = $debug;
        $this->environment = (string) $environment;
    }
}

