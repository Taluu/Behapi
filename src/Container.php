<?php declare(strict_types=1);
namespace Behapi;

use Psr\Container\ContainerInterface;

use Behat\Behat\HelperContainer\Exception\ServiceNotFoundException;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

use Http\Client\HttpClient;
use Http\Client\Common\PluginClient;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;

use Behapi\Http\PluginClientBuilder;
use Behapi\HttpHistory\History as HttpHistory;

use function in_array;
use function array_key_exists;

final class Container implements ContainerInterface
{
    /** @var object[] Instantiated services */
    private $services = [];

    /** @var string BaseURL for api requests */
    private $baseUrl;

    public function __construct(HttpHistory $history, string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->services[HttpHistory::class] = $history;
    }

    /** {@inheritDoc} */
    public function has($id)
    {
        static $services = [
            HttpClient::class,
            HttpHistory::class,
            StreamFactory::class,
            MessageFactory::class,
            PluginClientBuilder::class,
            EventDispatcherInterface::class,
        ];

        return in_array($id, $services);
    }

    /** {@inheritDoc} */
    public function get($id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        switch ($id) {
            case PluginClientBuilder::class:
                return $this->services[$id] = $this->getPluginClientBuilder();

            case HttpClient::class:
                return $this->services[$id] = $this->getHttpClient();

            case MessageFactory::class:
                return $this->services[$id] = MessageFactoryDiscovery::find();

            case StreamFactory::class:
                return $this->services[$id] = StreamFactoryDiscovery::find();

            case EventDispatcherInterface::class:
                return $this->services[$id] = new EventDispatcher;
        }

        throw new ServiceNotFoundException("Service {$id} is not available", $id);
    }

    private function getPluginClientBuilder(): PluginClientBuilder
    {
        $builder = new PluginClientBuilder;
        $uriFactory = UriFactoryDiscovery::find();
        $baseUri = $uriFactory->createUri($this->baseUrl);

        $builder->addPlugin(new ContentLengthPlugin);
        $builder->addPlugin(new BaseUriPlugin($baseUri));
        $builder->addPlugin(new HistoryPlugin($this->services[HttpHistory::class]));

        return $builder;
    }

    private function getHttpClient(): HttpClient
    {
        $http = HttpClientDiscovery::find();
        $builder = $this->get(PluginClientBuilder::class);

        return $builder->createClient($http);
    }
}
