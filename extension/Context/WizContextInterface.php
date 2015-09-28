<?php

namespace features\bootstrap\Extension\Context;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Wisembly\CoreBundle\Domain\Bag;

/**
 * Base context interface that all contexts must implement
 *
 * @author Baptiste Clavié <baptiste@wisembly.com>
 */
interface WizContextInterface
{
    const IDENTIFIER_ID   = 'id';
    const IDENTIFIER_HASH = 'hash';

    /** Sets the general bag */
    public function setBag(Bag $bag);

    /** Sets the parameter bag used in the contexts */
    public function setWizParameters(array $parameters);
}

