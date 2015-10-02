<?php
namespace Wisembly\Behat\Extension\Context;

use Behat\Behat\Context\Context;

/**
 * Base context interface that all contexts must implement
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
interface WizInterface extends Context
{
    /**
     * Initialize this WizContext
     *
     * @param string $environment Environment in which the behat suite is ran
     * @param boolean $debug Is the debug mode activated ?
     */
    public function initializeWiz($environment, $debug = false);
}

