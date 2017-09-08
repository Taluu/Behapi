<?php
namespace Behapi;

use Twig_Environment;
use Twig_Loader_Array;

use Psr\Http\Message\UriInterface;
use Interop\Container\ContainerInterface;

use Http\Message\UriFactory;
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
    /** @var object[] Instanciated services */
    private $services = [];

    /** @var string BaseURL for api requests */
    private $baseUrl;

    /** @var mixed[] Twig configuration (if needed) */
    private $twigConfig = [];

    /** @var Debug Debug status */
    private $debug;

    /** @var HttpHistory Latest requests / responses made */
    private $history;

    public function __construct(HttpHistory $history, Debug $debug, string $baseUrl, array $twigConfig = [])
    {
        $this->debug = $debug;
        $this->history = $history;

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

        return in_array($this->resolveAlias($id), $services);
    }

    /** {@inheritDoc} */
    public function get($id)
    {
        $id = $this->resolveAlias($id);

        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        switch ($id) {
            case HttpHistory::class:
                return $this->history;

            case HttpClient::class:
                return $this->getHttpClient();

            case MessageFactory::class:
                return $this->services[MessageFactory::class] = MessageFactoryDiscovery::find();

            case StreamFactory::class:
                return $this->services[StreamFactory::class] = StreamFactoryDiscovery::find();

            case Twig_Environment::class:
                return $this->getTwigService();
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
            new HistoryPlugin($this->history)
        ];

        $http = HttpClientDiscovery::find();

        return $this->services[HttpClient::class] = new PluginClient($http, $plugins);
    }

    private function getTwigService(): ?Twig_Environment
    {
        if (!class_exists(Twig_Environment::class)) {
            return $this->services[Twig_Environment::class] = null;
        }

        $options = [
            'debug' => $this->debug->getStatus(),
            'cache' => $this->twigConfig['cache'] ?? false,
            'autoescape' => $this->twigConfig['autoescape'] ?? false,
        ];

        return $this->services[Twig_Environment::class] = new Twig_Environment(new Twig_Loader_Array, $options);
    }

    private function resolveAlias(string $id): string
    {
        static $aliases = [
            'twig' => Twig_Environment::class,
            'http.client' => HttpClient::class,
            'http.history' => HttpHistory::class,
            'http.stream_factory' => StreamFactory::class,
            'http.message_factory' => MessageFactory::class,
        ];

        if (isset($aliases[$id])) {
            @trigger_error("Using {$id} is deprecated and will be removed, use {$aliases[$id]} instead", E_USER_DEPRECATED);

            return $aliases[$id];
        }

        return $id;
    }
}
