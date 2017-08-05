<?php
namespace Behapi\ServiceContainer;

use Throwable;
use InvalidArgumentException;

use Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    private $id;

    public function __construct(string $id, Throwable $previous = null)
    {
        parent::__construct("The service {$id} was not found in this container", 0, $previous);

        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}

