<?php
namespace Wisembly\Behat\Extension\Context;

trait TwigTrait
{
    /** @var  \Twig_Environment */
    private $twig;

    public function initializeTwig(\Twig_Environment $twig)
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
