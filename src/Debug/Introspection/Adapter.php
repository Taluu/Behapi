<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection;

use Psr\Http\Message\MessageInterface;

interface Adapter
{
    /**
     * Introspect useful information on a http message
     */
    public function introspect(MessageInterface $message): void;

    /**
     * Determines if this introspection supports the given http message
     */
    public function supports(MessageInterface $message): bool;
}
