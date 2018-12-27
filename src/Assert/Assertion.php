<?php declare(strict_types=1);
namespace Behapi\Assert;

use Assert\Assertion as Beberlei;

/**
 * Adds an alias to `noContent`, so that a `not` switch can work on `empty`.
 *
 * @method static bool allEmpty(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is empty for all values.
 * @method static bool nullOrEmpty(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is empty or that the value is null.
 */
abstract class Assertion extends Beberlei
{
    public static function empty($value, $message = null, $propertyPath = null)
    {
        parent::noContent($value, $message, $propertyPath);
    }
}
