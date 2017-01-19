<?php
namespace Behapi\Extension\Context;

use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler;

trait ProfilerAwareTrait
{
    /** @var Profiler */
    private $profiler;

    /** {@inheritDoc} */
    public function initializeProfiler(Profiler $profiler)
    {
        $this->profiler = $profiler;
    }

    /** {@inheritDoc} */
    public function getProfile($token)
    {
        $profile = $this->profiler->loadProfile($token);

        if (!$profile instanceof Profile) {
            throw new ProfileNotFoundException($token);
        }

        return $profile;
    }
}

