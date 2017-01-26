<?php
namespace Behapi\Extension;

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

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Tools\LastHistory;

use Behapi\Extension\ServiceContainer\NotFoundException;
use Behapi\Extension\ServiceContainer\ServiceNotAvailableException;

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

    /** @var LastHistory Last request / response made */
    private $history;

    public function __construct(LastHistory $history, Debug $debug, string $baseUrl, array $twigConfig = [])
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
            'debug',
            'http.history', 'http.client', 'http.message_factory', 'http.stream_factory'
        ];

        if (class_exists(Twig_Environment::class)) {
            $services[] = 'twig';
        }

        return in_array($id, $services);
    }

    /** {@inheritDoc} */
    public function get($id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        switch($id) {
            case 'debug':
                return $this->debug;

            case 'http.history':
                return $this->history;

            case 'http.client':
                return $this->getHttpClient();

            case 'http.message_factory':
                return $this->getHttpMessageFactory();

            case 'http.stream_factory':
                return $this->getHttpStreamFactory();

            case 'twig':
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

        return $this->services['http.client'] = new PluginClient($http, $plugins);
    }

    private function getHttpMessageFactory(): MessageFactory
    {
        return $this->services['http.message_factory'] = MessageFactoryDiscovery::find();
    }

    private function getHttpStreamFactory(): StreamFactory
    {
        return $this->services['http.stream_factory'] = StreamFactoryDiscovery::find();
    }

    private function getTwigService(): ?Twig_Environment
    {
        if (!class_exists(Twig_Environment::class)) {
            return $this->services['twig'] = null;
        }

        $options = [
            'debug' => $this->debug->getStatus(),
            'cache' => isset($this->config['cache']) ? $this->config['cache'] : false,
            'autoescape' => isset($this->config['autoescape']) ? $this->config['autoescape'] : false
        ];

        return $this->services['twig'] = new Twig_Environment(new Twig_Loader_Array, $options);
    }
}

