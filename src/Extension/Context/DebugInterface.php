<?php
namespace Behapi\Extension\Context;

use Behapi\Extension\Tools\Debug;

/**
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
interface DebugInterface
{
    /**
     * @param Debug $debug
     */
    public function setDebug(Debug $debug): void;
}

