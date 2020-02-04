<?php declare(strict_types=1);
namespace Behapi\Http;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

trait Builder
{
    /** @var PluginClientBuilder */
    private $builder;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /** @var RequestFactoryInterface */
    private $requestFactory;
}
