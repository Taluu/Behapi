<?php

namespace features\bootstrap\Extension\Guzzle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Guzzle\Common\Event;

class AuthenticationPlugin implements EventSubscriberInterface
{
    /** @var string */
    public $token = null;

    /** @var string */
    public $apiKey = null;

    public static function getSubscribedEvents()
    {
        return ['request.before_send' => 'onBeforeSend',

                'afterScenario'       => 'clearAuthentication',
                'afterOutlineExample' => 'clearAuthentication'];
    }

    public function onBeforeSend(Event $event)
    {
        if (null !== $this->token) {
            $event['request']->addHeader('Wisembly-Token', $this->token);
        }

        if (null !== $this->apiKey) {
            $event['request']->addHeader('Wisembly-Api-Key', $this->apiKey);
        }
    }

    public function clearAuthentication()
    {
        $this->token    = null;
        $this->apiKey   = null;
    }
}

