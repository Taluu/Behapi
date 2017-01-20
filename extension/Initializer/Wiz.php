<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Context\WizInterface;

/**
 * Initializes wiz contexts (which should be implemented by all contexts)
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Wiz implements ContextInitializer
{
    /** @var string */
    private $environment;

    /** @var Debug */
    private $debug;

    /*
     * @param string $environment Environment in which the behat suite is ran
     * @param boolean $debug Is the debug mode activated ?
     */
    public function __construct($environment, Debug $debug)
    {
        $this->debug = $debug;
        $this->environment = (string) $environment;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof WizInterface) {
            return;
        }

        $context->initializeWiz($this->environment, $this->debug);
    }
}

