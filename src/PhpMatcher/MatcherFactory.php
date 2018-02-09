<?php declare(strict_types=1);
namespace Behapi\PhpMatcher;

use Coduo\PHPMatcher\Matcher;
use Coduo\PHPMatcher\Factory\SimpleFactory;

/**
 * Removes the chain matcher, use only the JsonMatcher
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
final class MatcherFactory extends SimpleFactory
{
    /** @var Matcher\JsonMatcher */
    private $matcher;

    public function __construct()
    {
        $orMatcher = $this->buildOrMatcher();
        $this->matcher = new Matcher\JsonMatcher($orMatcher);
    }

    /** {@inheritDoc} */
    public function createMatcher()
    {
        return new Matcher($this->matcher);
    }
}
