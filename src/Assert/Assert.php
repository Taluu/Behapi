<?php declare(strict_types=1);
namespace Behapi\Assert;

use Assert\Assert as Beberlei;

/**
 * While the PR in links are not merged and released, let's use this assertion
 * class to satisfy our needs...
 *
 * @link https://github.com/beberlei/assert/pull/265
 * @link https://github.com/beberlei/assert/pull/272
 */
abstract class Assert extends Beberlei
{
    protected static $assertionClass = Assertion::class;

    public static function that($value, $defaultMessage = null, $defaultPropertyPath = null)
    {
        $assertionChain = new AssertionChain($value, $defaultMessage, $defaultPropertyPath);

        return $assertionChain->setAssertionClassName(static::$assertionClass);
    }
}
