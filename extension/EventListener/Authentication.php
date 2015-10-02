<?php
namespace Wisembly\Behat\Extension\EventListener;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\SubscriberInterface;

use Wisembly\Behat\Extension\Tools\Bag;

class Authentication implements SubscriberInterface
{
    /** @var Bag */
    private $bag;

    public function __construct(Bag $bag)
    {
        $this->bag = $bag;
    }

    public function getEvents()
    {
        return [
            'before' => ['onBeforeSend']
        ];
    }

    public function onBeforeSend(BeforeEvent $event)
    {
        $request = $event->getRequest();

        if (null !== $this->bag['token']) {
            $request->addHeader('Wisembly-Token', $this->bag['token']);
        }

        if (null !== $this->bag['api_key']) {
            $request->addHeader('Wisembly-Api-Key', $this->bag['api_key']);
        }
    }
}

