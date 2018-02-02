<?php
namespace Behapi;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;

use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Behapi\Tools\Debug;
use Behapi\Tools\HttpHistory as History;

use Behapi\Cli\DebugController;
use Behapi\EventListener\DebugHttp;
use Behapi\EventListener\HttpHistory;

/**
 * Extension which feeds the dependencies of behapi's features
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class Behapi implements Extension
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

                ->arrayNode('debug')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('formatter')
                            ->defaultValue('pretty')
                        ->end()

                        ->arrayNode('headers')
                            ->info('Headers to print in DebugHttp listener')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('request')
                                    ->info('Request headers to print in DebugHttp listener')
                                    ->defaultValue(['Content-Type'])
                                    ->prototype('scalar')->end()
                                ->end()

                                ->arrayNode('response')
                                    ->info('Response headers to print in DebugHttp listener')
                                    ->defaultValue(['Content-Type'])
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
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
        $container->register(Debug::class, Debug::class)
            ->addArgument($config['debug']['headers']['request'])
            ->addArgument($config['debug']['headers']['response'])

            ->setPublic(false)
        ;

        $container->register(History::class, History::class)
            ->setPublic(false)
        ;

        $container->register(DebugController::class, DebugController::class)
            ->addArgument(new Reference('output.manager'))
            ->addArgument(new Reference(Debug::class))
            ->addArgument($config['debug']['formatter'])

            ->setPublic(false)
            ->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 10])
        ;

        $container->register(DebugHttp::class, DebugHttp::class)
            ->addArgument(new Reference(Debug::class))
            ->addArgument(new Reference(History::class))

            ->setPublic(false)
            ->addTag('event_dispatcher.subscriber')
        ;

        $container->register(HttpHistory::class, HttpHistory::class)
            ->addArgument(new Reference(History::class))

            ->setPublic(false)
            ->addTag('event_dispatcher.subscriber')
        ;

        $this->loadContainer($container, $config);
    }

    /** {@inheritDoc} */
    public function process(ContainerBuilder $container)
    {
    }

    private function loadContainer(ContainerBuilder $container, array $config): void
    {
        $definition = $container->register(Container::class, Container::class);

        $definition
            ->addArgument(new Reference(History::class))
            ->addArgument(new Reference(Debug::class))
            ->addArgument($config['base_url'])
        ;

        $definition->setPublic(true);
        $definition->setShared(false);

        $definition->addTag(HelperContainerExtension::HELPER_CONTAINER_TAG);
    }
}
