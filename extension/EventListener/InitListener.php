<?php

namespace features\bootstrap\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\BaseScenarioEvent;

use Predis\ClientInterface;

use Wisembly\CoreBundle\Domain\Bag,
    Wisembly\CoreBundle\Services\Redis\RedisClient;

use features\bootstrap\Extension\Context\WizContextInterface;

/**
 * Test initializer listener
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class InitListener implements EventSubscriberInterface
{
    /** @var RedisClient */
    private $client;

    /** @var Bag */
    private $bag;

    public function __construct(RedisClient $client, Bag $bag)
    {
        $this->bag    = $bag;
        $this->client = $client;
    }

    /** {@inheritDoc} */
    public static function getSubscribedEvents()
    {
        return ['beforeSuite' => 'loadRefs',

                'beforeScenario'       => 'setRefIdentifier',
                'beforeOutlineExample' => 'setRefIdentifier',

                'afterScenario'       => 'resetBag',
                'afterOutlineExample' => 'resetBag'];
    }

    /**
     * Load all the stocked references
     *
     * Before running each test suite, load all the references and to what they
     * match into the Refs Bag
     */
    public function loadRefs()
    {
        $redis = $this->client->authenticate();

        if (!$redis instanceof ClientInterface) {
            return;
        }

        // Fetch fixtures references from redis
        $refs = json_decode($redis->GET('FIXTURES:REFS'), true) ?: [];

        $defaults = $this->bag->getDefaults();
        $defaults['references'] = $refs;

        $this->bag->setDefaults($defaults);
        $this->bag->set('references', $refs);

        $redis->disconnect();
        $redis->quit();
    }

    public function resetBag()
    {
        $this->bag->reset();
    }

    public function setRefIdentifier(BaseScenarioEvent $event)
    {
        $scenario = $event instanceof ScenarioEvent ? $event->getScenario() : $event->getOutline();

        $this->bag->set('identifier', $scenario->hasTag('identifier_hash') ? WizContextInterface::IDENTIFIER_HASH : WizContextInterface::IDENTIFIER_ID);
    }
}

