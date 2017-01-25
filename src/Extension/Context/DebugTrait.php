<?php
namespace Behapi\Extension\Context;

use Behapi\Extension\Tools\Debug;

trait DebugTrait
{
    /** @var Debug */
    private $debug;

    public function setDebug(Debug $debug): void
    {
        $this->debug = $debug;
    }
}

