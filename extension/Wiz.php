<?php
namespace Wisembly\Behat\Extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;

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

        $guzzle = $config['guzzle'];

        if (!isset($guzzle['redirect.disable'])) {
            $guzzle['redirect.disable'] = true;
        }

        if (!isset($guzzle['request.options'])) {
            $guzzle['request.options'] = ['exceptions' => false];
        }

        if (!isset($guzzle['request.options']['exceptions'])) {
            $guzzle['request.options']['exceptions'] = false;
        }

        $container->setParameter('behat.wiz.guzzle.parameters', $guzzle);
        $container->setParameter('behat.wiz.parameter.base_url', rtrim($config['base_url'], '/') . '/');

        unset($config['guzzle'], $config['base_url']);

        $container->setParameter('behat.wiz.parameters', $config);
    }

    /** {@inheritDoc} */
    public function process(ContainerBuilder $container)
    {
    }
}

