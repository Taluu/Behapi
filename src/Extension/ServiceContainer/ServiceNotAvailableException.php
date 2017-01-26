<?php
namespace Behapi\Extension\ServiceContainer;

use Throwable;
use InvalidArgumentException;

use Interop\Container\Exception\ContainerException;

class ServiceNotAvailableException extends InvalidArgumentException implements ContainerException
{
    private $id;

    private $reason;

    public function __construct(string $id, string $reason = null, Throwable $previous = null)
    {
        $message = "The service {$id} is not available";

        if (null !== $reason) {
            $message .= "({$reason})";
        }

        parent::__construct($message, 0, $previous);

        $this->id = $id;
        $this->reason = $reason;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}

