<?php
namespace Behapi\Context;

use stdClass;
use Datetime;

use Throwable;
use InvalidArgumentException;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Behapi\Tools\Assert;

abstract class AbstractJson implements Context
{
    /** @var PropertyAccessor */
    private $accessor;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Get the latest json response
     *
     * @return stdClass decoded json into an object
     */
    abstract protected function getJson();

    /**
     * Get the value for a path
     *
     * @param string $path Path to parse
     *
     * @return mixed
     * @throws AccessException path not valid
     */
    protected function getValue(string $path)
    {
        return $this->accessor->getValue($this->getJson(), $path);
    }

    /**
     * @Then the response should be a valid json response
     *
     * ---
     *
     * This method is built-on the default php's json extension. You should
     * overwrite it if you want to add supplementary checks or use something
     * else instead (such as Seldaek's JsonLint package).
     */
    public function responseIsValidjson()
    {
        $this->getJson();

        Assert::same(json_last_error(), JSON_ERROR_NONE, 'The latest json response should be a valid json response');
    }

    /** @Then :path should be accessible in the latest json response */
    public function pathShouldBeReadable(string $path)
    {
        Assert::true($this->accessor->isReadable($this->getJson(), $path), "The path $path should be a valid path");
    }

    /** @Then :path should not exist in the latest json response */
    public function pathShouldNotBeReadable(string $path)
    {
        Assert::false($this->accessor->isReadable($this->getJson(), $path), "The path $path should not be a valid path");
    }

    /** @Then in the json, :path should be equal to :expected */
    public function theJsonPathShouldBeEqualTo(string $path, $expected)
    {
        Assert::eq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should not be equal to :expected */
    public function theJsonPathShouldNotBeEqualTo(string $path, $expected)
    {
        Assert::notEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be: */
    public function theJsonPathShouldBePyString(string $path, PyStringNode $expected)
    {
        Assert::same($this->getValue($path), $expected->getRaw());
    }

    /** @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should be (?P<expected>true|false)$/ */
    public function theJsonPathShouldBe(string $path, string $expected)
    {
        Assert::same($this->getValue($path), 'true' === $expected);
    }

    /** @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should not be (?P<expected>true|false)$/ */
    public function theJsonPathShouldNotBe(string $path, string $expected)
    {
        Assert::notSame($this->getValue($path), 'true' === $expected);
    }

    /** @Then in the json, :path should be null */
    public function theJsonPathShouldBeNull(string $path)
    {
        Assert::null($this->getValue($path));
    }

    /** @Then in the json, :path should not be null */
    public function theJsonPathShouldNotBeNull(string $path)
    {
        Assert::notNull($this->getValue($path));
    }

    /** @Then in the json, :path should be empty */
    public function theJsonPathShouldBeEmpty(string $path)
    {
        Assert::isEmpty($this->getValue($path));
    }

    /** @Then in the json, :path should not be empty */
    public function theJsonPathShouldNotBeEmpty(string $path)
    {
        Assert::notEmpty($this->getValue($path));
    }

    /** @Then in the json, :path should contain :expected */
    public function theJsonPathContains(string $path, $expected)
    {
        Assert::contains($this->getValue($path), $expected);
    }

    /** @Then /^in the json, :path should not contain :expected */
    public function theJsonPathNotContains(string $path, $expected)
    {
        Assert::notContains($this->getValue($path), $expected);
    }

    /** @Then in the json, :path collection should contain an element with :value equal to :expected */
    public function theJsonPathCollectionContains(string $path, string $value, $expected)
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
    public function theJsonPathShouldBeAValidDate(string $path)
    {
        try {
            new Datetime($this->getValue($path));
        } catch (Throwable $t) {
            throw new InvalidArgumentException("$path does not contain a valid date");
        }
    }

    /** @Then in the json, :path should be greater than :expected */
    public function theJsonPathShouldBeGreaterThan(string $path, int $expected)
    {
        Assert::greaterThan($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be greater than or equal to :expected */
    public function theJsonPathShouldBeGreaterOrEqualThan(string $path, int $expected)
    {
        Assert::greaterThanEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be less than :expected */
    public function theJsonPathShouldBeLessThan(string $path, int $expected)
    {
        Assert::lessThan($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be less than or equal to :expected */
    public function theJsonPathShouldBeLessOrEqualThan(string $path, int $expected)
    {
        Assert::lessThanEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be an array */
    public function shouldBeAnArray(string $path)
    {
        Assert::isArray($this->getValue($path));
    }

    /** @Then in the json, :path should have at least :count element(s) */
    public function theJsonPathShouldHaveAtLeastElements(string $path, int $count)
    {
        $value = $this->getValue($path);

        Assert::isArray($value);
        Assert::greaterThanEq(count($value), $count);
    }

    /** @Then in the json, :path should have :count element(s) */
    public function theJsonPathShouldHaveElements(string $path, int $count)
    {
        Assert::count($this->getValue($path), $count);
    }

    /** @Then in the json, :path should have at most :count element(s) */
    public function theJsonPathShouldHaveAtMostElements(string $path, int $count)
    {
        $value = $this->getValue($path);

        Assert::isArray($value);
        Assert::lessThanEq(count($value), $count);
    }

    /** @Then in the json, :path should match :pattern */
    public function theJsonPathShouldMatch(string $path, string $pattern)
    {
        Assert::regex($this->getValue($path), $pattern);
    }

    /** @Then in the json, :path should not match :pattern */
    public function theJsonPathShouldNotMatch(string $path, string $pattern)
    {
        Assert::notRegex($this->getValue($path), $pattern);
    }

    /** @Then in the json, the root should be an array */
    public function rootShouldBeAnArray()
    {
        Assert::isArray($this->getJson());
    }

    /** @Then in the json, the root should have :count element(s) */
    public function theRootShouldHaveElements(int $count)
    {
        $value = $this->getJson();

        Assert::isArray($value);
        Assert::count($value, $count);
    }

    /** @Then in the json, the root should have at most :count element(s) */
    public function theRootShouldHaveAtMostElements(int $count)
    {
        $value = $this->getJson();

        Assert::isArray($value);
        Assert::lessThanEq($value, $count);
    }

    /** @Then in the json, :path should be a valid json encoded string */
    public function theJsonPathShouldBeAValidJsonEncodedString(string $path)
    {
        $value = json_decode($this->getValue($path));

        Assert::notNull($value);
        Assert::same(json_last_error(), JSON_ERROR_NONE);
    }
}
