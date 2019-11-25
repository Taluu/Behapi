<?php declare(strict_types=1);
namespace Behapi\Assert;

use Assert as Beberlei;

/**
 * Adds an alias to `noContent`, so that a `not` switch can work on `empty`.
 *
 * To remove once https://github.com/beberlei/assert/pull/272 has been merged and released.
 *
 * @method static bool allEmpty(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is empty for all values.
 * @method static bool nullOrEmpty(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is empty or that the value is null.
 *
 * @link https://github.com/beberlei/assert/pull/272
 */
abstract class Assertion extends Beberlei\Assertion
{
    /** @return bool */
    public static function empty($value, $message = null, ?string $propertyPath = null): bool
    {
        return parent::noContent($value, $message, $propertyPath);
    }
}
