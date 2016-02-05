<?php
namespace Wisembly\Behat\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Wisembly\Behat\Extension\Context\TwigInterface;

/**
 * Class TwigInitializer
 * @package Wisembly\Behat\Extension\Initializer
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class TwigInitializer implements ContextInitializer
{
    /** @var \Twig_Environment  */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof TwigInterface) {
            return;
        }

        $context->initializeTwig($this->twig);
    }
}
