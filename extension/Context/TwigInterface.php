<?php
namespace Behapi\Extension\Context;

use Twig_Environment;

interface TwigInterface
{
    public function initializeTwig(Twig_Environment $twig = null): void;

    public function renderString(string $string, array $context = []): string
}
