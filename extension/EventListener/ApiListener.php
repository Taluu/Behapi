<?php

namespace features\bootstrap\Extension\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\BaseScenarioEvent;

use features\bootstrap\Extension\Guzzle\AuthenticationPlugin,
    features\bootstrap\Extension\Context\ApiContextInterface;

/**
 * Listen to the ApiContext requests
 *
 * When a new scenario is met, it sets the api version it should use, and when
 * it finishes, unset the token.
 *
 * This listener should not be active before Behat 3 (as we have to make sure we
 * are using the right context, which in Behat 2 is always the "main context")
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class ApiListener implements EventSubscriberInterface
{
    /** @const integer */
    const DEFAULT_VERSION = 3;

    /** @var AuthenticationPlugin */
    private $plugin;

    public function __construct(AuthenticationPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public static function getSubscribedEvents()
    {
        return ['beforeScenario' => 'initVersion'];
    }

    public function initVersion(BaseScenarioEvent $event)
    {
        if (!$event->getContext() instanceof ApiContextInterface) {
            return;
        }

        $context  = $event->getContext();
        $version  = static::DEFAULT_VERSION;
        $scenario = $event instanceof ScenarioEvent ? $event->getScenario() : $event->getOutline();

        foreach ($scenario->getTags() as $tag) {
            $matches = [];

            if (preg_match('`api:?([1-9][0-9]*)`', $tag, $matches)) {
                $version = (int) $matches[1];
            }
        }

        $context->setApiVersion($version);
    }
}

