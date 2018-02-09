<?php declare(strict_types=1);
namespace Behapi\Http;

use Http\Client\Common\Plugin;
use Http\Client\Common\PluginClient;

final class PluginClientBuilder
{
    /** @var Plugin[] */
    private $plugins;

    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[] = $plugin;
    }

    public function createClient($client, array $options = []): PluginClient
    {
        return new PluginClient(
            $client,
            $this->plugins,
            $options
        );
    }
}
