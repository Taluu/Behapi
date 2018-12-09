<?php declare(strict_types=1);
namespace Behapi\Assert;

use Assert\Assertion as Beberlei;

use function sprintf;
use function mb_strpos;

/**
 * While the PR in links are not merged and released, let's use this assertion
 * class to satisfy our needs...
 *
 * @method static bool allEmpty(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is empty for all values.
 * @method static bool allIsCountable(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is countable for all values.
 * @method static bool allMaxCount(array|\Countable $countable, int $count, string $message = null, string $propertyPath = null) Assert that the countable does not have more than $count elements for all values.
 * @method static bool allMinCount(array|\Countable $countable, int $count, string $message = null, string $propertyPath = null) Assert that the countable has at least $count elements for all values.
 * @method static bool allNotContains(mixed $string, string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string does not contains a sequence of chars for all values.
 * @method static bool nullOrEmpty(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is empty or that the value is null.
 * @method static bool nullOrIsCountable(mixed $value, string|callable $message = null, string $propertyPath = null) Assert that value is countable or that the value is null.
 * @method static bool nullOrMaxCount(array|\Countable $countable, int $count, string $message = null, string $propertyPath = null) Assert that the countable does not have more than $count elements or that the value is null.
 * @method static bool nullOrMinCount(array|\Countable $countable, int $count, string $message = null, string $propertyPath = null) Assert that the countable has at least $count elements or that the value is null.
 * @method static bool nullOrNotContains(mixed $string, string $needle, string|callable $message = null, string $propertyPath = null, string $encoding = 'utf8') Assert that string does not contains a sequence of chars or that the value is null.
 *
 * @link https://github.com/beberlei/assert/pull/264
 * @link https://github.com/beberlei/assert/pull/266
 * @link https://github.com/beberlei/assert/pull/267
 */
abstract class Assertion extends Beberlei
{
    const INVALID_STRING_NOT_CONTAINS = 226;
    const INVALID_COUNTABLE = 227;
    const INVALID_MIN_COUNT = 228;
    const INVALID_MAX_COUNT = 229;

    /**
     * Assert that string does not contains a sequence of chars.
     *
     * @param mixed                $string
     * @param string               $needle
     * @param string|callable|null $message
     * @param string|null          $propertyPath
     * @param string               $encoding
     *
     * @return bool
     */
    public static function notContains($string, $needle, $message = null, $propertyPath = null, $encoding = 'utf8')
    {
        static::string($string, $message, $propertyPath);

        if (false !== mb_strpos($string, $needle, null, $encoding)) {
            $message = sprintf(
                static::generateMessage($message ?: 'Value "%s" contains "%s".'),
                static::stringify($string),
                static::stringify($needle)
            );

            throw static::createException($string, $message, static::INVALID_STRING_NOT_CONTAINS, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
        }

        return true;
    }

    /**
     * Assert that value is countable.
     *
     * @param mixed                $value
     * @param string|callable|null $message
     * @param string|null          $propertyPath
     *
     * @return bool
     */
    public static function isCountable($value, $message = null, $propertyPath = null)
    {
        if (function_exists('is_countable')) {
            $assert = is_countable($value);
        } else {
            $assert = is_array($value) || $value instanceof \Countable;
        }

        if (!$assert) {
            $message = \sprintf(
                static::generateMessage($message ?: 'Value "%s" is not an array and does not implement Countable.'),
                static::stringify($value)
            );

            throw static::createException($value, $message, static::INVALID_COUNTABLE, $propertyPath);
        }

        return true;
    }

    /**
     * Assert that the countable has at least $count elements.
     *
     * @param array|\Countable $countable
     * @param int              $count
     * @param string|null      $message
     * @param string|null      $propertyPath
     *
     * @return bool
     */
    public static function minCount($countable, $count, $message = null, $propertyPath = null)
    {
        if ($count < \count($countable)) {
            $message = \sprintf(
                static::generateMessage($message ?: 'List should have at least %d elements, but only has %d elements.'),
                static::stringify($count),
                static::stringify(\count($countable))
            );

            throw static::createException($countable, $message, static::INVALID_MIN_COUNT, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    /**
     * Assert that the countable does not have more than $count elements.
     *
     * @param array|\Countable $countable
     * @param int              $count
     * @param string|null      $message
     * @param string|null      $propertyPath
     *
     * @return bool
     */
    public static function maxCount($countable, $count, $message = null, $propertyPath = null)
    {
        if ($count > \count($countable)) {
            $message = \sprintf(
                static::generateMessage($message ?: 'List should have no more than %d elements, but has %d elements.'),
                static::stringify($count),
                static::stringify(\count($countable))
            );

            throw static::createException($countable, $message, static::INVALID_MAX_COUNT, $propertyPath, ['count' => $count]);
        }

        return true;
    }

    public static function empty($value, $message = null, $propertyPath = null)
    {
        parent::noContent($value, $message, $propertyPath);
    }
}
