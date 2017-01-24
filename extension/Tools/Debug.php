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

    public function setStatus(bool $status)
    {
        $this->status = $status;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }
}

