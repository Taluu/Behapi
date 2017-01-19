<?php
namespace Behapi\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Symfony\Component\HttpKernel\Profiler\Profiler;

use Behapi\Extension\Context\ProfilerAwareInterface;

class ProfilerAware implements ContextInitializer
{
    /** @var Profiler */
    private $profiler;

    public function __construct(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /** {@inheritDoc} */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof ProfilerAwareInterface) {
            return;
        }

        $context->initializeProfiler($this->profiler);
    }
}
