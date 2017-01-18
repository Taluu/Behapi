<?php
namespace Wisembly\Behat\Extension;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;

use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpKernel\Profiler\FileProfilerStorage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\History;

use Predis\Client as RedisClient;

use Twig_Environment;
use Twig_Loader_Chain;

use Wisembly\Behat\Extension\Tools\Bag;
use Wisembly\Behat\Extension\Tools\Debug;
use Wisembly\Behat\Extension\Tools\GuzzleFactory;

use Wisembly\Behat\Extension\Cli\DebugController;
use Wisembly\Behat\Extension\EventListener\Cleaner;

use Wisembly\Behat\Extension\Initializer\Api;
use Wisembly\Behat\Extension\Initializer\RedisAware;
use Wisembly\Behat\Extension\Initializer\ProfilerAware;
use Wisembly\Behat\Extension\Initializer\TwigInitializer;
use Wisembly\Behat\Extension\Initializer\RestAuthentication;
use Wisembly\Behat\Extension\Initializer\Wiz as WizInitializer;

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

                // TODO: add redis config here ?

                ->arrayNode('app')
                    ->children()
                        ->scalarNode('id')
                            ->info('Application ID to use')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('secret')
                            ->info('Application Secret to use')
                            ->isRequired()
                            ->cannotBeEmpty()
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

        $this->loadRedis($container);
        $this->loadSubscribers($container);
        $this->loadProfiler($container, $config['environment']);
        $this->loadTwig($container, $config['environment']);

        $this->loadInitializers($container, $config);
    }

    /** {@inheritDoc} */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('wiz.subscriber.cleaner');

        foreach ($container->findTaggedServiceIds('wiz.bag') as $id => $tags) {
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
        $container->register('wiz.debug', Debug::class);

        $container->register('wiz.controller.debug', DebugController::class)
            ->addArgument(new Reference('output.manager'))
            ->addArgument(new Reference('wiz.debug'))
            ->addArgument($config['debug_formatter'])
            ->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 10])
        ;
    }

    private function loadSubscribers(ContainerBuilder $container)
    {
        $container->register('wiz.subscriber.cleaner', Cleaner::class)
            ->addArgument(new Reference('guzzle.history'))
            ->addTag('event_dispatcher.subscriber')
        ;
    }

    private function loadRedis(ContainerBuilder $container)
    {
        // TODO: configure redis clients through the config tree
        $container->register('predis.client', RedisClient::class);
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

    private function loadProfiler(ContainerBuilder $container, $environment)
    {
        $storage = new Definition(FileProfilerStorage::class);
        $storage->addArgument(sprintf('file:%s/../../app/cache/%s/profiler', __DIR__, $environment));

        $container->register('profiler', Profiler::class)
            ->addArgument($storage);
    }

    private function loadInitializers(ContainerBuilder $container, array $config)
    {
        $container->register('wiz.initializer.wiz', WizInitializer::class)
            ->addArgument($config['environment'])
            ->addArgument(new Reference('wiz.debug'))
            ->addTag('context.initializer')
        ;

        $container->register('wiz.initializer.api', Api::class)
            ->addArgument(new Reference('guzzle.client'))
            ->addArgument(new Reference('guzzle.history'))
            ->addTag('context.initializer')
        ;

        $container->register('wiz.initializer.redis', RedisAware::class)
            ->addArgument(new Reference('predis.client'))
            ->addTag('context.initializer')
        ;

        $container->register('wiz.initializer.profiler', ProfilerAware::class)
            ->addArgument(new Reference('profiler'))
            ->addTag('context.initializer')
        ;

        $container->register('wiz.initializer.authentication', RestAuthentication::class)
            ->addArgument($config['app']['id'])
            ->addArgument($config['app']['secret'])
            ->addTag('context.initializer')
        ;

        if (class_exists(Twig_Environment::class)) {
            $container->register('wiz.initializer.twig', TwigInitializer::class)
                ->addArgument(new Reference('twig'))
                ->addTag('context.initializer')
            ;
        }
    }

    private function loadTwig(ContainerBuilder $container, $environment)
    {
        if (!class_exists(Twig_Environment::class)) {
            return;
        }

        $container->register('twig.loader', Twig_Loader_Chain::class);

        $container->register('twig', Twig_Environment::class)
            ->addArgument(new Reference('twig.loader'))
            ->addArgument([
                'debug' => 'dev' === $environment,
                'cache' => sprintf('%s/../../app/cache/%s/twig/behat', __DIR__, $environment),
                'autoescape' => false
            ]);
    }
}

