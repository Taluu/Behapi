<?php
namespace Wisembly\Behat\Extension\Context;

use Twig_Environment;

interface TwigInterface
{
    public function initializeTwig(Twig_Environment $twig = null);
}
