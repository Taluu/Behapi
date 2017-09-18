<?php
namespace Behapi;

use Twig_Environment;
use Twig_Loader_Array;

use Psr\Container\ContainerInterface;

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

use Behapi\Tools\Debug;
use Behapi\Tools\HttpHistory;

use Behapi\ServiceContainer\NotFoundException;
use Behapi\ServiceContainer\ServiceNotAvailableException;

class Container implements ContainerInterface
{
    /** @var object[] Instantiated services */
    private $services = [];

    /** @var string BaseURL for api requests */
    private $baseUrl;

    /** @var mixed[] Twig configuration (if needed) */
    private $twigConfig = [];

    /** @var Debug Debug status */
    private $debug;

    public function __construct(HttpHistory $history, Debug $debug, string $baseUrl, array $twigConfig = [])
    {
        $this->debug = $debug;
        $this->services[HttpHistory::class] = $history;

        $this->baseUrl = $baseUrl;
        $this->twigConfig = $twigConfig;
    }

    /** {@inheritDoc} */
    public function has($id)
    {
        static $services = [
            HttpClient::class,
            HttpHistory::class,
            StreamFactory::class,
            MessageFactory::class,
        ];

        if (class_exists(Twig_Environment::class)) {
            $services[] = Twig_Environment::class;
        }

        return in_array($id, $services);
    }

    /** {@inheritDoc} */
    public function get($id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        switch ($id) {
            case HttpClient::class:
                return $this->services[$id] = $this->getHttpClient();

            case MessageFactory::class:
                return $this->services[$id] = MessageFactoryDiscovery::find();

            case StreamFactory::class:
                return $this->services[$id] = StreamFactoryDiscovery::find();

            case Twig_Environment::class:
                return $this->services[$id] = $this->getTwigService();
        }

        throw new NotFoundException($id);
    }

    private function getHttpClient(): HttpClient
    {
        $uriFactory = UriFactoryDiscovery::find();
        $baseUri = $uriFactory->createUri($this->baseUrl);

        $plugins = [
            new ContentLengthPlugin,
            new BaseUriPlugin($baseUri),
            new HistoryPlugin($this->services[HttpHistory::class])
        ];

        $http = HttpClientDiscovery::find();

        return new PluginClient($http, $plugins);
    }

    private function getTwigService(): ?Twig_Environment
    {
        if (!class_exists(Twig_Environment::class)) {
            return null;
        }

        $options = [
            'debug' => $this->debug->getStatus(),
            'cache' => $this->twigConfig['cache'] ?? false,
            'autoescape' => $this->twigConfig['autoescape'] ?? false,
        ];

        return new Twig_Environment(new Twig_Loader_Array, $options);
    }
}
