<?php
namespace Wisembly\Behat\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\History;

use Wisembly\Behat\Extension\Tools\GuzzleFactory;
use Wisembly\Behat\Extension\EventListener\Cleaner;
use Wisembly\Behat\Extension\EventListener\Authentication;

/**
 * WizContext feeder
 *
 * Extension which feeds the main and extended context for wisembly's behat
 * features
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class Wiz implements Extension
{
    /** {@inheritDoc} */
    public function getConfigKey()
    {
        return 'wiz_extension';
    }

    /** {@inheritDoc} */
    public function configure(ArrayNodeDefinition $builder)
    {
        $node
            ->children()
                ->scalarNode('base_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->enumNode('environment')
                    ->values(['dev', 'test'])
                    ->defaultValue('dev')
                ->end()

                ->booleanNode('debug')
                    ->defaultFalse()
                ->end()

                ->arrayNode('guzzle')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')
                ->end()
            ->end()
        ->end();

    }

    /** {@inheritDoc} */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /** {@inheritDoc} */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
        $loader->load('config.xml');

        $this->loadGuzzle($container, $config['guzzle'], $config['base_url']);
        unset($config['guzzle'], $config['base_url']);

        $this->loadSubscribers($container);

        $container->setParameter('wiz.parameters', $config);
    }

    /** {@inheritDoc} */
    public function process(ContainerBuilder $container)
    {
    }

    private function loadSubscribers(ContainerBuilder $container)
    {
        $container->register('wiz.subscriber.cleaner', Cleaner::class)
            ->addArgument(new Reference('wiz.bag'))
            ->addArgument(new Reference('guzzle.history'))
            ->addTag('event_dispatcher.subscriber');
    }

    private function loadGuzzle(ContainerBuilder $container, array $config, $baseUrl)
    {
        $config = array_replace_recursive(
            [
                'base_url' => $baseUrl,

                'defaults' => [
                    'allow_redirects' => false,
                    'exceptions' => false
                ]
            ],
            $config
        );

        $container->register('guzzle.history'. History::class)
            ->addArgument(1); // note : limit on the last request only ?

        $factory = new Definition(GuzzleFactory::class);
        $factory
            ->addMethodCall('addSubscriber', [
                new Reference('guzzle.history')
            ])

            ->addMethodCall('addSubscriber', [
                new Definition(Authentication::class, [
                    new Reference('wiz.bag')
                ])
            ])
        ;

        $container->register('guzzle.client')
            ->addArgument($config)
            ->setFactory([$factory, 'getClient']);
    }
}

