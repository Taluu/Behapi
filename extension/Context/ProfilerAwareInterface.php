<?php
namespace Behapi\Extension\Context;

use Symfony\Component\HttpKernel\Profiler\Profiler;

interface ProfilerAwareInterface
{
    /** Initializes the context with a Profiler instance */
    public function initializeProfiler(Profiler $profiler);

    /** Get the profile for a specific token */
    public function getProfile($token);
}

