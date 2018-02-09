<?php declare(strict_types=1);
namespace Behapi\Tools;

use Webmozart\Assert\Assert as webmozart;

use function sprintf;
use function preg_match;

/**
 * Assert
 *
 * Use while https://github.com/webmozart/assert/pull/58 isn't merged
 *
 * @method static void nullOrNotRegx($value, $pattern, $message = '')
 * @method static void allNotRegex($values, $pattern, $message = '')
 */
final class Assert extends webmozart
{
    public static function notRegex($value, $pattern, $message = '')
    {
        if (!preg_match($pattern, $value)) {
            return;
        }

        static::reportInvalidArgument(sprintf(
            $message ?: 'Expected a value to not match %s',
            static::valueToString($pattern)
        ));
    }
}
