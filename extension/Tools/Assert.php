<?php
namespace Behapi\Extension\Tools;

use Webmozart\Assert\Assert as webmozart;

/**
 * Assert
 *
 * Use while https://github.com/webmozart/assert/pull/37 isn't merged
 *
 * @method static void nullOrNotContains($value, $subString, $message = '')
 * @method static void allContains($values, $subString, $message = '')
 */
class Assert extends webmozart
{
    public static function notContains($value, $subString, $message = '')
    {
        if (false !== strpos($value, $subString)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to not contain %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($subString)
            ));
        }
    }
}

