<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behapi\Extension\Context\TwigInterface;

use Twig_Environment;

/**
 * Class TwigInitializer
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class TwigInitializer implements ContextInitializer
{
    /** @var Twig_Environment  */
    private $twig;

    public function __construct(Twig_Environment $twig = null)
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
