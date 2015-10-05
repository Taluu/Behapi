<?php
namespace Wisembly\Behat\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Wisembly\Behat\Extension\Context\WizInterface;

/**
 * Initializes wiz contexts (which should be implemented by all contexts)
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class Wiz implements ContextInitializer
{
    /** @var string */
    private $environment;

    private $debug = false;

    /*
     * @param string $environment Environment in which the behat suite is ran
     * @param boolean $debug Is the debug mode activated ?
     */
    public function __construct($environment, $debug = false)
    {
        $this->debug = (bool) $debug;
        $this->environment = (string) $environment;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof WizContextInterface) {
            return;
        }

        $context->initializeWiz($this->environment, $this->debug);
    }
}

