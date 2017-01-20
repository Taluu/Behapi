<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Behapi\Extension\Tools\Debug;
use Behapi\Extension\Context\TwigInterface;

use Twig_Environment;
use Twig_Loader_Array;

/**
 * Class TwigInitializer
 * @author Manuel Raynaud <manu@raynaud.io>
 */
class TwigInitializer implements ContextInitializer
{
    /** @var Twig_Environment  */
    private $twig;

    public function __construct(Debug $debug, array $config)
    {
        $this->twig = new Twig_Environment(new Twig_Loader_Array, [
            'debug' => $debug->getStatus(),
            'cache' => isset($config['cache']) ? new $config['cache'] : false,
            'autoescape' => isset($config['autoescape']) ? $config['autoescape'] : false
        ]);
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
