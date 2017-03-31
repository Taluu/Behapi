<?php
namespace Behapi\Extension;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;

use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Tools\HttpHistory as History;

use Behapi\Extension\Cli\DebugController;
use Behapi\Extension\EventListener\DebugHttp;
use Behapi\Extension\EventListener\HttpHistory;

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
        $container->register('behapi.debug', Debug::class)
            ->addArgument($config['debug']['headers']['request'])
            ->addArgument($config['debug']['headers']['response'])
        ;

        $container->register('behapi.history', History::class);

        $container->register('behapi.controller.debug', DebugController::class)
            ->addArgument(new Reference('output.manager'))
            ->addArgument(new Reference('behapi.debug'))
            ->addArgument($config['debug']['formatter'])
            ->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 10])
        ;

        $container->register('behapi.subscriber.debug', DebugHttp::class)
            ->addArgument(new Reference('behapi.debug'))
            ->addArgument(new Reference('behapi.history'))
            ->addTag('event_dispatcher.subscriber')
        ;

        $container->register('behapi.subscriber.http_history', HttpHistory::class)
            ->addArgument(new Reference('behapi.history'))
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
        $definition = $container->register('behapi.container', Container::class);

        $definition
            ->addArgument(new Reference('behapi.history'))
            ->addArgument(new Reference('behapi.debug'))
            ->addArgument($config['base_url'])
            ->addArgument($config['twig'])
        ;

        $definition->addTag(HelperContainerExtension::HELPER_CONTAINER_TAG);

        if (method_exists($definition, 'setShared')) { // Symfony 2.8
            $definition->setShared(false);
        } else {
            $definition->setScope(ContainerBuilder::SCOPE_PROTOTYPE);
        }
    }
}
