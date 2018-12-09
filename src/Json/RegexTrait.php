<?php declare(strict_types=1);
namespace Behapi\Json;

use InvalidArgumentException;

use Webmozart\Assert\Assert;

trait RegexTrait
{
    abstract protected function getValue(?string $path);

    /** @Then in the json, :path should match :pattern */
    final public function the_json_path_should_match(string $path, string $pattern): void
    {
        Assert::regex($this->getValue($path), $pattern);
    }

    /**
     * @Then in the json, :path should not match :pattern
     *
     * -----
     *
     * Note :: The body of this assertion should be replaced by a
     * `Assert::notRegex` as soon as the Assert's PR
     * https://github.com/webmozart/assert/pull/58 is merged and released.
     */
    final public function the_json_path_should_not_match(string $path, string $pattern): void
    {
        if (!preg_match($pattern, $this->getValue($path), $matches, PREG_OFFSET_CAPTURE)) {
            // it's all good, it is supposed not to match. :}
            return;
        }

        throw new InvalidArgumentException("The value matches {$pattern} at offset {$matches[0][1]}");
    }
}
