<?php
namespace Behapi\Extension\Context;

use Twig_Environment;

interface TwigInterface
{
    /**
     * Renders a string using Twig
     *
     * @param string $string String to process
     * @param array $context context to give to twig to render the string
     */
    public function renderString(string $string, array $context = []): string;
}

