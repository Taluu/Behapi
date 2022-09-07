<?php declare(strict_types=1);
namespace Behapi;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;

use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Behapi\Debug;
use Behapi\HttpHistory;

/**
 * Extension which feeds the dependencies of behapi's features
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class Behapi implements Extension
{
    private const DEBUG_INTROSPECTION_TAG = 'behapi.debug.introspection';

    public function getConfigKey(): string
    {
        return 'behapi';
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress ReservedWord
     * @psalm-suppress MixedMethodCall
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('base_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('debug')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('introspection')
                            ->info('Debug Introspection configuration')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('var_dumper')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('json')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->arrayNode('types')
                                                    ->info('Types to be used in the json var-dumper adapter')
                                                    ->defaultValue(['application/json'])
                                                    ->prototype('scalar')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('headers')
                            ->info('Headers to print in Debug Http introspection adapters')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('request')
                                    ->info('Request headers to print in Debug Http introspection adapters')
                                    ->defaultValue(['Content-Type'])
                                    ->prototype('scalar')->end()
                                ->end()

                                ->arrayNode('response')
                                    ->info('Response headers to print in Debug Http introspection adapters')
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

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $container->register(HttpHistory\History::class, HttpHistory\History::class)
            ->setPublic(false)
        ;

        $container->register(HttpHistory\Listener::class, HttpHistory\Listener::class)
            ->addArgument(new Reference(HttpHistory\History::class))

            ->setPublic(false)
            ->addTag('event_dispatcher.subscriber')
        ;

        $this->loadDebugServices($container, $config['debug']);
        $this->loadContainer($container, $config);
    }

    public function process(ContainerBuilder $container): void
    {
        $dumpers = [];

        foreach ($container->findTaggedServiceIds(self::DEBUG_INTROSPECTION_TAG) as $id => $tags) {
            foreach ($tags as $attributes) {
                $priority = $attributes['priority'] ?? 0;
                $dumpers[$priority][] = new Reference($id);
            }
        }

        krsort($dumpers);

        $container->getDefinition(Debug\Listener::class)
            ->addArgument(array_merge(...$dumpers));
    }

    private function loadContainer(ContainerBuilder $container, array $config): void
    {
        $definition = $container->register(Container::class, Container::class);

        $definition
            ->addArgument(new Reference(HttpHistory\History::class))
            ->addArgument($config['base_url'])
        ;

        $definition->setPublic(true);
        $definition->setShared(false);

        $definition->addTag(HelperContainerExtension::HELPER_CONTAINER_TAG);
    }

    /**
     * @psalm-suppress MixedArrayAccess
     */
    private function loadDebugServices(ContainerBuilder $container, array $config): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->register(Debug\Status::class, Debug\Status::class)
            ->setPublic(false)
        ;

        $container->register(Debug\CliController::class, Debug\CliController::class)
            ->addArgument(new Reference(Debug\Status::class))

            ->setPublic(false)
            ->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 10])
        ;

        $container->register(Debug\Listener::class, Debug\Listener::class)
            ->addArgument(new Reference(Debug\Status::class))
            ->addArgument(new Reference(HttpHistory\History::class))

            ->setPublic(false)
            ->addTag('event_dispatcher.subscriber')
        ;

        $adapters = [
            Debug\Introspection\Request\EchoerAdapter::class => [-100, [$config['headers']['request']]],
            Debug\Introspection\Response\EchoerAdapter::class => [-100, [$config['headers']['response']]],

            Debug\Introspection\Request\VarDumperAdapter::class => [-80, [$config['headers']['request']]],
            Debug\Introspection\Response\VarDumperAdapter::class => [-80, [$config['headers']['response']]],

            Debug\Introspection\Request\VarDumper\JsonAdapter::class => [-75, [$config['headers']['request'], $config['introspection']['var_dumper']['json']['types']]],
            Debug\Introspection\Response\VarDumper\JsonAdapter::class => [-75, [$config['headers']['response'], $config['introspection']['var_dumper']['json']['types']]],
        ];

        foreach ($adapters as $adapter => [$priority, $args]) {
            $def = $container->register($adapter, $adapter)
                ->addTag(self::DEBUG_INTROSPECTION_TAG, ['priority' => $priority])
            ;

            foreach ($args as $arg) {
                $def->addArgument($arg);
            }
        }
    }
}
