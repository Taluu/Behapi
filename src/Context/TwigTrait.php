<?php
namespace Behapi\Context;

use Twig_Environment;

trait TwigTrait
{
    /** @var  Twig_Environment */
    private $twig;

    public function renderString(string $string, array $context = []): string
    {
        if (null === $this->twig) {
            return $string;
        }

        $key = sprintf('__behapi_tpl__%s', hash('sha256', $string));

        // this is assuming that the loader is Twig_Loader_Array
        // as this was privately set in the initializer, it should be OK
        // to assume that this is still a Twig_Loader_Array
        $loader = $this->twig->getLoader();
        $loader->setTemplate($key, $string);

        return $this->twig->load($key)->render($context);
    }
}

