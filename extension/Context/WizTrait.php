<?php
namespace Wisembly\Behat\Extension\Context;

trait WizTrait
{
    /** @var string environment which under the behat test suite is run */
    private $environment = 'dev';

    /** @var boolean */
    private $debug = true;

    public function initializeWiz($environment, $debug = false)
    {
        $this->debug = (bool) $debug;
        $this->environment = (string) $environment;
    }
}

