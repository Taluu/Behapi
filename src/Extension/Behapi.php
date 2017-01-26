<?php
namespace Behapi\Extension;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;

use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Twig_Environment;

use Psr\Http\Message\UriInterface;

use Http\Client\HttpClient;

use Http\Message\UriFactory;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\UriFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

use Http\Client\Common\PluginClient;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\Common\Plugin\ContentLengthPlugin;

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Tools\LastHistory;

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
        $container->register('behapi.debug', Debug::class);
        $container->register('behapi.history', LastHistory::class);

        $container->register('behapi.controller.debug', DebugController::class)
            ->addArgument(new Reference('output.manager'))
            ->addArgument(new Reference('behapi.debug'))
            ->addArgument($config['debug_formatter'])
            ->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 10])
        ;

        $container->register('behapi.subscriber.cleaner', Cleaner::class)
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

