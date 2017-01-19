<?php
namespace Behapi\Extension\Context;

use Predis\ClientInterface;

interface RedisAwareInterface
{
    /** Initializes the context with a PRedis instance */
    public function initializeRedis(ClientInterface $redis);
}

