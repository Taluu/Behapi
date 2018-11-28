<?php declare(strict_types=1);
namespace Behapi\Json;

use stdClass;
use DateTime;

use Throwable;
use InvalidArgumentException;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context as BehatContext;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Webmozart\Assert\Assert;

use function count;
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

    protected function getValue(?string $path)
    {
        $json = $this->getJson();

        return $path === null ? $json : $this->accessor->getValue($json, $path);
    }

    /** @Then :path should be accessible in the latest json response */
    final public function path_should_be_readable(string $path): void
    {
        Assert::true($this->accessor->isReadable($this->getValue(null), $path), "The path $path should be a valid path");
    }

    /** @Then :path should not exist in the latest json response */
    final public function path_should_not_be_readable(string $path): void
    {
        Assert::false($this->accessor->isReadable($this->getValue(null), $path), "The path $path should not be a valid path");
    }

    /**
     * @Then in the json, :path should be equal to :expected
     * @Then in the json, :path should :not be equal to :expected
     */
    final public function the_json_path_should_be_equal_to(string $path, ?string $not = null, $expected): void
    {
        $assert = [Assert::class, $not !== null ? 'notEq' : 'eq'];
        assert(is_callable($assert));

        $assert($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be: */
    final public function the_json_path_should_be_py_string(string $path, PyStringNode $expected): void
    {
        Assert::same($this->getValue($path), $expected->getRaw());
    }

    /**
     * @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should be (?P<expected>true|false)$/
     * @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should :not be (?P<expected>true|false)$/
     */
    final public function the_json_path_should_be(string $path, ?string $not = null, string $expected): void
    {
        $assert = [Assert::class, $not !== null ? 'notSame' : 'same'];
        assert(is_callable($assert));

        $assert($this->getValue($path), 'true' === $expected);
    }

    /**
     * @Then in the json, :path should be null
     * @Then in the json, :path should :not be null
     */
    final public function the_json_path_should_be_null(string $path, ?string $not = null): void
    {
        $assert = [Assert::class, $not !== null ? 'notNull' : 'null'];
        assert(is_callable($assert));

        $assert($this->getValue($path));
    }

    /**
     * @Then in the json, :path should be empty
     * @Then in the json, :path should :not be empty
     */
    final public function the_json_path_should_be_empty(string $path, ?string $not = null): void
    {
        $assert = [Assert::class, $not !== null ? 'notEmpty' : 'isEmpty'];
        assert(is_callable($assert));

        $assert($this->getValue($path));
    }

    /**
     * @Then in the json, :path should contain :expected
     * @Then in the json, :path should :not contain :expected
     */
    final public function the_json_path_contains(string $path, ?string $not = null, $expected): void
    {
        $assert = [Assert::class, $not !== null ? 'notContains' : 'contains'];
        assert(is_callable($assert));

        $assert($this->getValue($path), $expected);
    }

    /** @Then in the json, :path collection should contain an element with :value equal to :expected */
    final public function the_json_path_collection_contains(string $path, string $value, $expected): void
    {
        $collection = $this->getValue($path);
        Assert::isIterable($collection);

        foreach ($collection as $element) {
            if ($expected === $this->accessor->getValue($element, $value)) {
                return;
            }
        }

        throw new InvalidArgumentException("$path collection does not contain an element with $value equal to $expected");
    }

    /** @Then in the json, :path should be a valid date(time) */
    final public function the_json_path_should_be_a_valid_date(string $path): void
    {
        try {
            new DateTime($this->getValue($path));
        } catch (Throwable $t) {
            throw new InvalidArgumentException("$path does not contain a valid date");
        }
    }

    /** @Then in the json, :path should be greater than :expected */
    final public function the_json_path_should_be_greater_than(string $path, int $expected): void
    {
        Assert::greaterThan($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be greater than or equal to :expected */
    final public function the_json_path_should_be_greater_or_equal_than(string $path, int $expected): void
    {
        Assert::greaterThanEq($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be less than :expected */
    final public function the_json_path_should_be_less_than(string $path, int $expected): void
    {
        Assert::lessThan($this->getValue($path), $expected);
    }

    /** @Then in the json, :path should be less than or equal to :expected */
    final public function the_json_path_should_be_less_or_equal_than(string $path, int $expected): void
    {
        Assert::lessThanEq($this->getValue($path), $expected);
    }

    /**
     * @Then in the json, the root should be an array
     * @Then in the json, :path should be an array
     */
    final public function should_be_an_array(?string $path = null): void
    {
        Assert::isArray($this->getValue($path));
    }

    /**
     * @Then in the json, the root collection should have at least :count element(s)
     * @Then in the json, :path collection should have at least :count element(s)
     */
    final public function the_json_collection_should_have_at_least_elements(?string $path = null, int $count): void
    {
        $value = $this->getValue($path);

        Assert::isCountable($value);
        Assert::minCount($value, $count);
    }

    /**
     * @Then in the json, the root collection should have :count element(s)
     * @Then in the json, :path collection should have :count element(s)
     */
    final public function the_json_path_should_have_elements(?string $path = null, int $count): void
    {
        $value = $this->getValue($path);

        Assert::isCountable($value);
        Assert::count($value, $count);
    }

    /**
     * @Then in the json, the root collection should have at most :count element(s)
     * @Then in the json, :path collection should have at most :count element(s)
     */
    final public function the_json_path_should_have_at_most_elements(?string $path = null, int $count): void
    {
        $value = $this->getValue($path);

        Assert::isCountable($value);
        Assert::maxCount($value, $count);
    }

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

    /** @Then in the json, :path should be a valid json encoded string */
    final public function the_json_path_should_be_a_valid_json_encoded_string(string $path): void
    {
        $value = json_decode($this->getValue($path));

        Assert::notNull($value);
        Assert::same(json_last_error(), JSON_ERROR_NONE);
    }

    /**
     * @Then /^in the json, all the elements in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should have a(?:n?) "(?P<property>(?:[^"]|\\")*)" property$/
     * @Then /^in the json, all the elements in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) have a(?:n?) "(?P<property>(?:[^"]|\\")*)" property$/
     **/
    final public function the_json_path_elements_in_collection_should_have_a_property(string $path, ?string $not = null, string $property): void
    {
        $assert = [Assert::class, $not !== null ? 'allPropertyNotExists' : 'allPropertyExists'];
        assert(is_callable($assert));

        $assert($this->getValue(empty($path) ? null : $path), $property);
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should be equal to "(?P<expected>(?:[^"]|\\")*)"$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) be equal to "(?P<expected>(?:[^"]|\\")*)"$/
     */
    final public function the_json_each_elements_in_collection_should_be_equal_to(string $path, string $property, ?string $not = null, string $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);
        Assert::isIterable($values);

        $assert = [Assert::class, $not !== null ? 'notSame' : 'same'];
        assert(is_callable($assert));

        foreach ($values as $element) {
            $assert($this->accessor->getValue($element, $property), $expected);
        }
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should be (?P<expected>true|false)$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) be (?P<expected>true|false)$/
     */
    final public function the_json_each_elements_in_collection_should_be_bool(string $path, string $property, ?string $not = null, string $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);
        Assert::isIterable($values);

        $assert = [Assert::class, $not !== null ? 'notSame' : 'same'];
        assert(is_callable($assert));

        foreach ($values as $element) {
            $assert($this->accessor->getValue($element, $property), $expected === 'true');
        }
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should be equal to (?P<expected>[0-9]+)$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) be equal to (?P<expected>[0-9]+)$/
     */
    final public function the_json_each_elements_in_collection_should_be_equal_to_int(string $path, string $property, ?string $not = null, int $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);
        Assert::isIterable($values);

        $assert = [Assert::class, $not !== null ? 'notSame' : 'same'];
        assert(is_callable($assert));

        foreach ($values as $element) {
            $assert($this->accessor->getValue($element, $property), $expected);
        }
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should contain "(?P<expected>(?:[^"]|\\")*)"$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) contain "(?P<expected>(?:[^"]|\\")*)"$/
     **/
    final public function the_json_each_elements_in_collection_should_contain(string $path, string $property, ?string $not = null, string $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);
        Assert::isIterable($values);

        $assert = [Assert::class, $not !== null ? 'notSame' : 'same'];
        assert(is_callable($assert));

        foreach ($values as $element) {
            $assert($this->accessor->getValue($element, $property), $expected);
        }
    }
}
