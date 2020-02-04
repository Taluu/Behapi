<?php declare(strict_types=1);
namespace Behapi;

use Psr\Container\ContainerInterface;

use Behat\Behat\HelperContainer\Exception\ServiceNotFoundException;

use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Http\Discovery\UriFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;

use Behapi\Http\PluginClientBuilder;
use Behapi\HttpHistory\History as HttpHistory;

use function bin2hex;
use function in_array;
use function random_bytes;
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
            HttpHistory::class,
            StreamFactory::class,
            MessageFactory::class,
            PluginClientBuilder::class,
        ];

        return in_array($id, $services, true);
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

            case MessageFactory::class:
                return $this->services[$id] = MessageFactoryDiscovery::find();

            case StreamFactory::class:
                return $this->services[$id] = StreamFactoryDiscovery::find();
        }

        throw new ServiceNotFoundException("Service {$id} is not available", $id);
    }

    private function getPluginClientBuilder(): PluginClientBuilder
    {
        $builder = new PluginClientBuilder;
        $uriFactory = UriFactoryDiscovery::find();
        $baseUri = $uriFactory->createUri($this->baseUrl);

        assert($this->services[HttpHistory::class] instanceof HttpHistory);

        // use randomized strings so that these cannot be removed (safety)
        $builder->addPlugin(bin2hex(random_bytes(10)), new ContentLengthPlugin);
        $builder->addPlugin(bin2hex(random_bytes(10)), new BaseUriPlugin($baseUri));
        $builder->addPlugin(bin2hex(random_bytes(10)), new HistoryPlugin($this->services[HttpHistory::class]));

        return $builder;
    }
}
