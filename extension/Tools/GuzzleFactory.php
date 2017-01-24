<?php
namespace Behapi\Extension\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Event\SubscriberInterface;

class GuzzleFactory
{
    /** @var EventSubscriberInterface[] */
    private $subscribers;

    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        $this->subscribers[] = $subscriber;
    }

    public function getClient(array $config): Client
    {
        $client = new Client($config);

        foreach ($this->subscribers as $subscriber) {
            $client->getEmitter()->attach($subscriber);
        }

        return $client;
    }
}

