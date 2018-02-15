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
