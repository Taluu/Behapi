<?php declare(strict_types=1);
namespace Behapi;

use Behat\Behat\HelperContainer\Exception\ServiceNotFoundException;

use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;

use Behapi\Http\PluginClientBuilder;
use Behapi\HttpHistory\History as HttpHistory;

use Http\Discovery\Psr17FactoryDiscovery;

use Psr\Container\ContainerInterface;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function in_array;
use function array_key_exists;

final class Container implements ContainerInterface
{
    /** @var object[] Instantiated services */
    private $services = [];

    /** @var string BaseURL for api requests */
    private $baseUrl;

    private const SERVICES = [
        HttpHistory::class,
        PluginClientBuilder::class,
        StreamFactoryInterface::class,
        RequestFactoryInterface::class,
    ];

    public function __construct(HttpHistory $history, string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->services[HttpHistory::class] = $history;
    }

    public function has($id)
    {
        return in_array($id, self::SERVICES, true);
    }

    public function get($id)
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }

        switch ($id) {
            case PluginClientBuilder::class:
                return $this->services[$id] = $this->getPluginClientBuilder();

            case RequestFactoryInterface::class:
                return $this->services[$id] = Psr17FactoryDiscovery::findRequestFactory();

            case StreamFactoryInterface::class:
                return $this->services[$id] = Psr17FactoryDiscovery::findStreamFactory();
        }

        throw new ServiceNotFoundException("Service {$id} is not available", $id);
    }

    private function getPluginClientBuilder(): PluginClientBuilder
    {
        $builder = new PluginClientBuilder;
        $uriFactory = Psr17FactoryDiscovery::findUrlFactory();

        $baseUri = $uriFactory->createUri($this->baseUrl);
        $httpHistory = $this->services[HttpHistory::class];

        assert($httpHistory instanceof HttpHistory);

        $builder = $builder->addPlugin(new ContentLengthPlugin);
        $builder = $builder->addPlugin(new BaseUriPlugin($baseUri));
        $builder = $builder->addPlugin(new HistoryPlugin($httpHistory));

        return $builder;
    }
}
