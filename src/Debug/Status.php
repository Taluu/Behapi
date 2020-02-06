<?php declare(strict_types=1);
namespace Behapi\Debug;

/**
 * Object containing the debug configuration (status, headers)
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class Status
{
    /** @var bool */
    private $enabled = false;

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
