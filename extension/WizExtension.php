<?php
namespace features\bootstrap\Extension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,

    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Behat\Extension\ExtensionInterface;

/**
 * WizContext feeder
 *
 * Extension which feeds the main and extended context for wisembly's behat
 * features
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class WizExtension implements ExtensionInterface
{
    /** {@inheritDoc} */
    public function load(array $config, ContainerBuilder $container)
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
    public function getConfig(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('base_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->enumNode('environment')
                    ->values(['prod', 'dev', 'test'])
                    ->defaultValue('prod')
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
    public function getCompilerPasses()
    {
        // no compiler pass at the current time
        return [];
    }
}

