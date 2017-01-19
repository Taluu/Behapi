<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Predis\ClientInterface as Redis;

use Behapi\Extension\Context\RedisAwareInterface;

class RedisAware implements ContextInitializer
{
    /** @var Redis */
    private $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof RedisAwareInterface) {
            return;
        }

        $context->initializeRedis($this->redis);
    }
}

