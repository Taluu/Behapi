<?php

namespace features\bootstrap\Extension\Context\Initializer;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Initializer\InitializerInterface;

use Wisembly\CoreBundle\Domain\Bag;

use features\bootstrap\Extension\Context\WizContextInterface;

/**
 * Initializes all the contexts
 *
 * Give them access to some parameters for this extension and also to the
 * references bag
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
class WizInitializer implements InitializerInterface
{
    /** @var array */
    private $parameters;

    /** @var Bag */
    private $bag;

    public function __construct(Bag $bag, array $parameters)
    {
        $this->bag        = $bag;
        $this->parameters = $parameters;
    }

    /** {@inheritDoc} */
    public function supports(ContextInterface $context)
    {
        return $context instanceof WizContextInterface;
    }

    /** {@inheritDoc} */
    public function initialize(ContextInterface $context)
    {
        $context->setBag($this->bag);
        $context->setWizParameters($this->parameters);
    }
}

