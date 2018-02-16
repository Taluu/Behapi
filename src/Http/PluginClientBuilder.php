<?php declare(strict_types=1);
namespace Behapi\Http;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;

final class PluginClientBuilder
{
    /** @var Plugin[] */
    private $plugins;

    /** @var ?PluginClient */
    private $client;

    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[] = $plugin;
        $this->client = null;
    }

    /** @param Plugin|string $plugin Plugin or plugin class to remove */
    public function removePlugin($element): void
    {
        $callback = $element instanceof Plugin
            ? function (Plugin $plugin) use ($element) { return $plugin !== $element; }
            : function (Plugin $plugin) use ($element) { return get_class($plugin) !== $element; }
        ;

        $this->plugins = array_filter($this->plugins, $callback);
        $this->client = null;
    }

    public function createClient($client, array $options = []): PluginClient
    {
        if (null === $this->client) {
            $this->client = new PluginClient(
                $client,
                $this->plugins,
                $options
            );
        }

        return $this->client;
    }
}
