<?php
namespace Behapi\Tools;

use Coduo\PHPMatcher\Matcher;
use Coduo\PHPMatcher\Factory\SimpleFactory;

/**
 * Removes the chain matcher, use only the JsonMatcher
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class BehapiFactory extends SimpleFactory
{
    /** @var Matcher\JsonMatcher */
    private $matcher;

    /** {@inheritDoc} */
    public function createMatcher()
    {
        if (null === $this->matcher) {
            $orMatcher = $this->buildOrMatcher();

            $this->matcher = new Matcher\JsonMatcher($orMatcher);
        }

        return new Matcher($this->matcher);
    }
}
