<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection;

use Psr\Http\Message\MessageInterface;

interface Adapter
{
    /**
     * Introspect useful information on a http message
     *
     * @param string[] $headers Headers to introspect
     */
    public function introspect(MessageInterface $message, array $headers): void;

    /**
     * Determines if this introspection supports the given http message
     */
    public function supports(MessageInterface $message): bool;
}
