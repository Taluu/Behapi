<?php declare(strict_types=1);
namespace Behapi\Http;

use Throwable;
use InvalidArgumentException;

final class PluginNotFound extends InvalidArgumentException
{
    /** @var string */
    private $name;

    public function __construct(string $name, Throwable $previous = null)
    {
        $this->name = $name;

        parent::__construct("Plugin {$name} was not found.", 0, $previous);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
