<?php declare(strict_types=1);
namespace Behapi\Debug;

/**
 * Object containing the debug configuration (status, headers)
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class Configuration
{
    /** @var bool */
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
