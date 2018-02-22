<?php declare(strict_types=1);
namespace Behapi\Json;

use stdClass;
use Datetime;

use Throwable;
use InvalidArgumentException;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context as BehatContext;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Webmozart\Assert\Assert;

use function preg_match;
use function json_last_error;

use const JSON_ERROR_NONE;
use const PREG_OFFSET_CAPTURE;

abstract class AbstractContext implements BehatContext
{
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    abstract protected function getJson();

    protected function getValue(string $path)
    {
        return $this->accessor->getValue($this->getJson(), $path);
    }

    /** @Then :path should be accessible in the latest json response */
    public function pathShouldBeReadable(string $path): void
    {
        Assert::true($this->accessor->isReadable($this->getJson(), $path), "The path $path should be a valid path");
    }

    /** @Then :path should not exist in the latest json response */
    public function pathShouldNotBeReadable(string $path): void
    {
        Assert::false($this->accessor->isReadable($this->getJson(), $path), "The path $path should not be a valid path");
    }

    /** @Then in the json, :path should be equal to :expected */
    public function theJsonPathShouldBeEqualTo(string $path, $expected): void
    {
        Assert::eq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should not be equal to :expected */
    public function theJsonPathShouldNotBeEqualTo(string $path, $expected): void
    {
        Assert::notEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be: */
    public function theJsonPathShouldBePyString(string $path, PyStringNode $expected): void
    {
        Assert::same($this->getValue($path), $expected->getRaw());
    }

    /** @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should be (?P<expected>true|false)$/ */
    public function theJsonPathShouldBe(string $path, string $expected): void
    {
        Assert::same($this->getValue($path), 'true' === $expected);
    }

    /** @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should not be (?P<expected>true|false)$/ */
    public function theJsonPathShouldNotBe(string $path, string $expected): void
    {
        Assert::notSame($this->getValue($path), 'true' === $expected);
    }

    /** @Then in the json, :path should be null */
    public function theJsonPathShouldBeNull(string $path): void
    {
        Assert::null($this->getValue($path));
    }

    /** @Then in the json, :path should not be null */
    public function theJsonPathShouldNotBeNull(string $path): void
    {
        Assert::notNull($this->getValue($path));
    }

    /** @Then in the json, :path should be empty */
    public function theJsonPathShouldBeEmpty(string $path): void
    {
        Assert::isEmpty($this->getValue($path));
    }

    /** @Then in the json, :path should not be empty */
    public function theJsonPathShouldNotBeEmpty(string $path): void
    {
        Assert::notEmpty($this->getValue($path));
    }

    /** @Then in the json, :path should contain :expected */
    public function theJsonPathContains(string $path, $expected): void
    {
        Assert::contains($this->getValue($path), $expected);
    }

    /** @Then /^in the json, :path should not contain :expected */
    public function theJsonPathNotContains(string $path, $expected): void
    {
        Assert::notContains($this->getValue($path), $expected);
    }

    /** @Then in the json, :path collection should contain an element with :value equal to :expected */
    public function theJsonPathCollectionContains(string $path, string $value, $expected): void
    {
        $collection = $this->accessor->getValue($this->getJson(), $path);

        foreach ($collection as $element) {
            if ($expected === $this->accessor->getValue($element, $value)) {
                return;
            }
        }

        throw new InvalidArgumentException("$path collection does not contain an element with $value equal to $expected");
    }

    /** @Then in the json, :path should be a valid date(time) */
    public function theJsonPathShouldBeAValidDate(string $path): void
    {
        try {
            new Datetime($this->getValue($path));
        } catch (Throwable $t) {
            throw new InvalidArgumentException("$path does not contain a valid date");
        }
    }

    /** @Then in the json, :path should be greater than :expected */
    public function theJsonPathShouldBeGreaterThan(string $path, int $expected): void
    {
        Assert::greaterThan($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be greater than or equal to :expected */
    public function theJsonPathShouldBeGreaterOrEqualThan(string $path, int $expected): void
    {
        Assert::greaterThanEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be less than :expected */
    public function theJsonPathShouldBeLessThan(string $path, int $expected): void
    {
        Assert::lessThan($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be less than or equal to :expected */
    public function theJsonPathShouldBeLessOrEqualThan(string $path, int $expected): void
    {
        Assert::lessThanEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be an array */
    public function shouldBeAnArray(string $path): void
    {
        Assert::isArray($this->getValue($path));
    }

    /** @Then in the json, :path should have at least :count element(s) */
    public function theJsonPathShouldHaveAtLeastElements(string $path, int $count): void
    {
        $value = $this->getValue($path);

        Assert::isArray($value);
        Assert::greaterThanEq(count($value), $count);
    }

    /** @Then in the json, :path should have :count element(s) */
    public function theJsonPathShouldHaveElements(string $path, int $count): void
    {
        Assert::count($this->getValue($path), $count);
    }

    /** @Then in the json, :path should have at most :count element(s) */
    public function theJsonPathShouldHaveAtMostElements(string $path, int $count): void
    {
        $value = $this->getValue($path);

        Assert::isArray($value);
        Assert::lessThanEq(count($value), $count);
    }

    /** @Then in the json, :path should match :pattern */
    public function theJsonPathShouldMatch(string $path, string $pattern): void
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
    public function theJsonPathShouldNotMatch(string $path, string $pattern): void
    {
        if (!preg_match($pattern, $this->getValue($path), $matches, PREG_OFFSET_CAPTURE)) {
            // it's all good, it is supposed not to match. :}
            return;
        }

        throw new InvalidArgumentException("The value matches {$pattern} at offset {$matches[0][1]}");
    }

    /** @Then in the json, the root should be an array */
    public function rootShouldBeAnArray(): void
    {
        Assert::isArray($this->getJson());
    }

    /** @Then in the json, the root should have :count element(s) */
    public function theRootShouldHaveElements(int $count): void
    {
        $value = $this->getJson();

        Assert::isArray($value);
        Assert::count($value, $count);
    }

    /** @Then in the json, the root should have at most :count element(s) */
    public function theRootShouldHaveAtMostElements(int $count): void
    {
        $value = $this->getJson();

        Assert::isArray($value);
        Assert::lessThanEq($value, $count);
    }

    /** @Then in the json, :path should be a valid json encoded string */
    public function theJsonPathShouldBeAValidJsonEncodedString(string $path): void
    {
        $value = json_decode($this->getValue($path));

        Assert::notNull($value);
        Assert::same(json_last_error(), JSON_ERROR_NONE);
    }
}
