<?php
namespace Behapi\Extension\Context;

use Twig_Environment;

trait TwigTrait
{
    /** @var  Twig_Environment */
    private $twig;

    public function initializeTwig(Twig_Environment $twig = null)
    {
        $this->twig = $twig;
    }

    public function renderString($string)
    {
        if (null === $this->twig) {
            return $string;
        }

        return $this->twig->createTemplate($string)->render([]);
    }
}
