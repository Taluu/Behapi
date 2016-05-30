<?php
namespace Wisembly\Behat\Extension\Context;

use Wisembly\Behat\Extension\Tools\Debug;

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

