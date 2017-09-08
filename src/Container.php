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
            'http.client',
            HttpClient::class,

            'http.history',
            HttpHistory::class,

            'http.stream_factory',
            StreamFactory::class,

            'http.message_factory',
            MessageFactory::class,
        ];

        if (class_exists(Twig_Environment::class)) {
            $services[] = 'twig';
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
            case 'http.history':
            case HttpHistory::class:
                return $this->history;

            case 'http.client':
            case HttpClient::class:
                return $this->getHttpClient();

            case MessageFactory::class:
            case 'http.message_factory':
                return $this->services[MessageFactory::class] = $this->services['http.message_factory'] = MessageFactoryDiscovery::find();

            case StreamFactory::class:
            case 'http.stream_factory':
                return $this->services[StreamFactory::class] = $this->services['http.stream_factory'] = StreamFactoryDiscovery::find();

            case 'twig':
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

        return $this->services[HttpClient::class] = $this->services['http.client'] = new PluginClient($http, $plugins);
    }

    private function getTwigService(): ?Twig_Environment
    {
        if (!class_exists(Twig_Environment::class)) {
            return $this->services['twig'] = null;
            return $this->services[Twig_Environment::class] = null;
        }

        $options = [
            'debug' => $this->debug->getStatus(),
            'cache' => $this->twigConfig['cache'] ?? false,
            'autoescape' => $this->twigConfig['autoescape'] ?? false
        ];

        return $this->services[Twig_Environment::class] = $this->services['twig'] = new Twig_Environment(new Twig_Loader_Array, $options);
    }
}
