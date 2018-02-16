<?php declare(strict_types=1);
namespace Behapi\Http;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;

use function array_values;

final class PluginClientBuilder
{
    /** @var Plugin[] */
    private $plugins;

    /** @var ?PluginClient */
    private $client;

    public function addPlugin(string $name, Plugin $plugin): void
    {
        $this->plugins[$name] = $plugin;
        $this->client = null;
    }

    public function removePlugin(string $name): void
    {
        unset($this->plugins[$name]);
        $this->client = null;
    }

    public function getPlugin(string $name): Plugin
    {
        if (!isset($this->plugins[$name])) {
            throw new PluginNotFound($name);
        }

        return $this->plugins[$name];
    }

    public function createClient($client, array $options = []): PluginClient
    {
        if (null === $this->client) {
            $this->client = new PluginClient(
                $client,
                array_values($this->plugins),
                $options
            );
        }

        return $this->client;
    }
}
