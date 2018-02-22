<?php declare(strict_types=1);
namespace Behapi\Http;

trait Builder
{
    /** @var PluginClientBuilder */
    private $builder;

    /** @var StreamFactory */
    private $streamFactory;

    /** @var MessageFactory */
    private $messageFactory;
}
