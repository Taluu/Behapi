<?php declare(strict_types=1);
namespace Behapi\Http;

use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

trait Builder
{
    /** @var PluginClientBuilder */
    private $builder;

    /** @var StreamFactory */
    private $streamFactory;

    /** @var MessageFactory */
    private $messageFactory;
}
