<?php declare(strict_types=1);
namespace Behapi\Assert;

use Assert\AssertionChain as Beberlei;

/**
 * PR needed while some PRs are not merged and released on upstream. :}
 *
 * @method AssertionChain empty(string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string is empty.
 * @method AssertionChain maxCount(int $count, string $message = null, string $propertyPath = null) Assert that the countable does not have more than $count elements.
 * @method AssertionChain minCount(int $count, string $message = null, string $propertyPath = null) Assert that the countable has at least $count elements.
 * @method AssertionChain notContains(string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string does not contains a sequence of chars.
 * @method AssertionChain isCountable(string|callable $message = null, string $propertyPath = null) Assert that value is countable.
 *
 * @link https://github.com/beberlei/assert/pull/264
 * @link https://github.com/beberlei/assert/pull/265
 * @link https://github.com/beberlei/assert/pull/266
 * @link https://github.com/beberlei/assert/pull/267
 */
class AssertionChain extends Beberlei
{
    /**
     * Perform a negative assertion.
     *
     * @var bool
     */
    private $not = false;

    public function __call($methodName, $args)
    {
        if ($this->not) {
            $methodName = "not{$methodName}";
        }

        return parent::__call($methodName, $args);
    }

    /**
     * Switch chain into negative mode.
     *
     * @return AssertionChain
     */
    public function not()
    {
        $this->not = true;
        return $this;
    }
}
