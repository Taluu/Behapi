<?php declare(strict_types=1);
namespace Behapi\Assert;

use Assert as Beberlei;

/**
 * PR needed while some PRs are not merged and released on upstream. :}
 *
 * @method AssertionChain empty(string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string is empty.
 *
 * @link https://github.com/beberlei/assert/pull/265
 */
class AssertionChain extends Beberlei\AssertionChain
{
    /**
     * Perform a negative assertion.
     *
     * @var bool
     */
    private $not = false;

    public function __call($methodName, $args): Beberlei\AssertionChain
    {
        if ($this->not && strtolower(substr($methodName, 0, 3)) !== 'not') {
            $methodName = "not{$methodName}";
        }

        return parent::__call($methodName, $args);
    }

    /**
     * Switch chain into negative mode.
     */
    public function not(): self
    {
        $this->not = true;
        return $this;
    }
}
