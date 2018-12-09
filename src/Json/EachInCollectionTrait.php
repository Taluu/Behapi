<?php declare(strict_types=1);
namespace Behapi\Json;

use Webmozart\Assert\Assert;

trait EachInCollectionTrait
{
    abstract protected function getValue(?string $path);

    /**
     * @Then /^in the json, each elements in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should have a(?:n?) "(?P<property>(?:[^"]|\\")*)" property$/
     * @Then /^in the json, each elements in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) have a(?:n?) "(?P<property>(?:[^"]|\\")*)" property$/
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
