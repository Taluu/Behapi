<?php
namespace Wisembly\Behat\Extension\EventListener;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\SubscriberInterface;

use Wisembly\CoreBundle\Domain\Bag;

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

        if ($this->bag->has('token')) {
            $request->addHeader('Wisembly-Token', $this->bag->get('token'));
        }

        if ($this->bag->has('api_key')) {
            $request->addHeader('Wisembly-Api-Key', $this->bag->get('api_key'));
        }
    }
}

