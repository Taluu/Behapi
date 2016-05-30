<?php
namespace Wisembly\Behat\Extension\Context;

use Wisembly\Behat\Extension\Tools\Debug;

/**
 * Base context interface that all contexts must implement
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
interface WizInterface
{
    /**
     * Initialize this WizContext
     *
     * @param string $environment Environment in which the behat suite is ran
     * @param Debug $debug
     */
    public function initializeWiz($environment, Debug $debug);
}

