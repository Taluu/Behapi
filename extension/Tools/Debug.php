<?php
namespace Behapi\Extension\Tools;

/**
 * Simple value object true / false so it can be injected into the different
 * services. This is not pretty, no it isn't...
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Debug
{
    private $status = false;

    public function setStatus($status)
    {
        $this->status = (bool) $status;
    }

    public function getStatus()
    {
        return (bool) $this->status;
    }
}

