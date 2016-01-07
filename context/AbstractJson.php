<?php
namespace Wisembly\Behat\Context;

use Datetime;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

use PHPUnit_Framework_Assert as Assert;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
    protected function getValue($path)
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

        Assert::assertSame(JSON_ERROR_NONE, json_last_error(), 'The latest json response should be a valid json response');
    }

    /** @Then :path should be accessible in the latest json response */
    public function pathShouldBeReadable($path, $default = null)
    {
        Assert::assertTrue($this->accessor->isReadable($this->getJson(), $path), "The path $path should be a valid path");
    }

    /** @Then :path should not exist in the latest json response */
    public function pathShouldNotBeReadable($path)
    {
        Assert::assertFalse($this->accessor->isReadable($this->getJson(), $path), "The path $path should not be a valid path");
    }

    /** @Then in the json, :path should be equal to :expected */
    public function theJsonPathShouldBeEqualTo($path, $expected)
    {
        Assert::assertEquals($expected, $this->getValue($path));
    }

    /** @Then in the json, :path should not be equal to :expected */
    public function theJsonPathShouldNotBeEqualTo($path, $expected)
    {
        Assert::assertNotEquals($expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be: */
    public function theJsonPathShouldBePyString($path, PyStringNode $expected)
    {
        Assert::assertSame($expected, $this->getValue($path)->getRaw());
    }

    /** @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should be (?P<expected>true|false)$/ */
    public function theJsonPathShouldBe($path, $expected)
    {
        Assert::assertSame('true' === $expected, $this->getValue($path));
    }

    /** @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should not be (?P<expected>true|false)$/ */
    public function theJsonPathShouldNotBe($path, $expected)
    {
        Assert::assertNotSame('true' === $expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be null */
    public function theJsonPathShouldBeNull($path)
    {
        Assert::assertNull($this->getValue($path));
    }

    /** @Then in the json, :path should not be null */
    public function theJsonPathShouldNotBeNull($path)
    {
        Assert::assertNotNull($this->getValue($path));
    }

    /** @Then in the json, :path should be empty */
    public function theJsonPathShouldBeEmpty($path)
    {
        Assert::assertEmpty($this->getValue($path));
    }

    /** @Then in the json, :path should not be empty */
    public function theJsonPathShouldNotBeEmpty($path)
    {
        Assert::assertNotEmpty($this->getValue($path));
    }

    /** @Then in the json, :path should contain :expected */
    public function theJsonPathContains($path, $expected)
    {
        Assert::assertContains($expected, $this->getValue($path));
    }

    /** @Then /^in the json, :path should not contain :expected */
    public function theJsonPathNotContains($path, $expected)
    {
        Assert::assertNotContains($expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be a valid date(time) */
    public function theJsonPathShouldBeAValidDate($path)
    {
        try {
            new Datetime($this->getValue($path));
        } catch (Exception $e) {
            Assert::fail("$path does not contain a valid date");
        }
    }

    /** @Then in the json, :path should be a valid :format formatted date(time) */
    public function theJsonPatShouldHaveThisFormat($path, $format)
    {
        Assert::assertNotFalse(Datetime::createFromFormat($format, $this->getValue($path)));
    }

    /** @Then in the json, :path should be greater than :expected */
    public function theJsonPathShouldBeGreaterThan($path, $expected)
    {
        Assert::assertGreaterThan((int) $expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be greater than or equal to :expected */
    public function theJsonPathShouldBeGreaterOrEqualThan($path, $expected)
    {
        Assert::assertGreaterThanOrEqual((int) $expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be less than :expected */
    public function theJsonPathShouldBeLessThan($path, $expected)
    {
        Assert::assertLessThan((int) $expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be less than or equal to :expected */
    public function theJsonPathShouldBeLessOrEqualThan($path, $expected)
    {
        Assert::assertLessThanOrEqual((int) $expected, $this->getValue($path));
    }

    /** @Then in the json, :path should be an array */
    public function shouldBeAnArray($path)
    {
        Assert::assertInternalType('array', $this->getValue($path));
    }

    /** @Then in the json, :path should have at least :count element(s) */
    public function theJsonPathShouldHaveAtLeastElements($path, $count)
    {
        $value = $this->getValue($path);

        Assert::assertInternalType('array', $value);
        Assert::assertGreaterThanOrEqual((int) $count, count($value));
    }

    /** @Then in the json, :path should have :count element(s) */
    public function theJsonPathShouldHaveElements($path, $count)
    {
        Assert::assertCount((int) $count, $this->getValue($path));
    }

    /** @Then in the json, :path should have at most :count element(s) */
    public function theJsonPathShouldHaveAtMostElements($path, $count)
    {
        $value = $this->getValue($path);

        Assert::assertInternalType('array', $value);
        Assert::assertLessThanOrEqual((int) $count, count($value));
    }

    /** @Then in the json, the root should be an array */
    public function rootShouldBeAnArray()
    {
        Assert::assertInternalType('array', $this->getJson());
    }

    /** @Then in the json, the root should have :count element(s) */
    public function theRootShouldHaveElements($count)
    {
        $value = $this->getJson();

        Assert::assertInternalType('array', $value);
        Assert::assertCount((int) $count, $value);
    }

    /** @Then in the json, the root should have at most :count element(s) */
    public function theRootShouldHaveAtMostElements($count)
    {
        $value = $this->getJson();

        Assert::assertInternalType('array', $value);
        Assert::assertLessThanOrEqual((int) $count, count($value));
    }
}

