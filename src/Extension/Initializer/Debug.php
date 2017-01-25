<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Behapi\Extension\Context\DebugInterface;
use Behapi\Extension\Tools\Debug as DebugStatus;

/**
 * Set the debug mode on Debug contexts
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Debug implements ContextInitializer
{
    /** @var Debug */
    private $debug;

    /*
     * @param Debug $debug Debug status
     */
    public function __construct(DebugStatus $debug)
    {
        $this->debug = $debug;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof DebugInterface) {
            return;
        }

        $context->setDebug($this->debug);
    }
}

