<?php
namespace Behapi\Extension;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\History;

use Twig_Environment;

use Behapi\Extension\Tools\Bag;
use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Tools\GuzzleFactory;

use Behapi\Extension\Cli\DebugController;
use Behapi\Extension\EventListener\Cleaner;

use Behapi\Extension\Initializer\Api;
use Behapi\Extension\Initializer\TwigInitializer;
use Behapi\Extension\Initializer\RestAuthentication;
use Behapi\Extension\Initializer\Debug as DebugInitializer;

/**
 * Extension which feeds the dependencies of behapi's features
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class Behapi implements Extension
{
    /** {@inheritDoc} */
    public function getConfigKey()
    {
        return 'behapi';
    }

    /** {@inheritDoc} */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('base_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->enumNode('environment')
                    ->values(['dev', 'test'])
                    ->defaultValue('dev')
                ->end()

                ->scalarNode('debug_formatter')
                    ->defaultValue('pretty')
                ->end()

                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('cache')
                            ->defaultNull()
                            ->beforeNormalization()
                                ->ifEmpty()
                                ->thenUnset()
                            ->end()
                            ->validate()
                            ->ifTrue(function ($v) { return !is_dir($v); })
                                ->thenInvalid('Directory does not exist')
                            ->end()
                        ->end()
                        ->enumNode('autoescape')
                            ->values([false, 'html', 'js', 'name'])
                            ->defaultFalse()
                        ->end()
                    ->end()
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
        $this->loadDebug($container, $config);

        $this->loadGuzzle($container, $config['base_url']);
        unset($config['base_url']);

        $this->loadSubscribers($container);

        $this->loadInitializers($container, $config);
    }

    /** {@inheritDoc} */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('behapi.subscriber.cleaner');

        foreach ($container->findTaggedServiceIds('behapi.bag') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['reset']) || true !== $tag['reset']) {
                    continue;
                }

                $definition->addMethodCall('addBag', [new Reference($id)]);
            }
        }
    }

    private function loadDebug(ContainerBuilder $container, array $config)
    {
        $container->register('behapi.debug', Debug::class);

        $container->register('behapi.controller.debug', DebugController::class)
            ->addArgument(new Reference('output.manager'))
            ->addArgument(new Reference('behapi.debug'))
            ->addArgument($config['debug_formatter'])
            ->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 10])
        ;
    }

    private function loadSubscribers(ContainerBuilder $container)
    {
        $container->register('behapi.subscriber.cleaner', Cleaner::class)
            ->addArgument(new Reference('guzzle.history'))
            ->addTag('event_dispatcher.subscriber')
        ;
    }

    private function loadGuzzle(ContainerBuilder $container, $baseUrl)
    {
        $config = [
            'base_url' => $baseUrl,

            'defaults' => [
                'allow_redirects' => false,
                'exceptions' => false
            ]
        ];

        $container->register('guzzle.history', History::class)
            ->addArgument(1); // note : limit on the last request only ?

        $factory = new Definition(GuzzleFactory::class);
        $factory
            ->addMethodCall('addSubscriber', [
                new Reference('guzzle.history')
            ])
        ;

        $container->register('guzzle.client', Client::class)
            ->addArgument($config)
            ->setFactory([$factory, 'getClient']);
    }

    private function loadInitializers(ContainerBuilder $container, array $config)
    {
        $container->register('behapi.initializer.debug', DebugInitializer::class)
            ->addArgument(new Reference('behapi.debug'))
            ->addTag('context.initializer')
        ;

        $container->register('behapi.initializer.api', Api::class)
            ->addArgument(new Reference('guzzle.client'))
            ->addArgument(new Reference('guzzle.history'))
            ->addTag('context.initializer')
        ;

        if (class_exists(Twig_Environment::class)) {
            $container->register('behapi.initializer.twig', TwigInitializer::class)
                ->addArgument(new Reference('behapi.debug'))
                ->addArgument($config['twig'])
                ->addTag('context.initializer')
            ;
        }
    }
}

