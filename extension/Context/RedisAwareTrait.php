<?php
namespace Behapi\Extension\Context;

use Predis\ClientInterface;

trait RedisAwareTrait
{
    /** @var ClientInterface */
    private $redis;

    /** {@inheritdoc} */
    public function initializeRedis(ClientInterface $redis)
    {
        $this->redis = $redis;
    }
}

