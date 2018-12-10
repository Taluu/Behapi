<?php declare(strict_types=1);
namespace Behapi\Json;

use Symfony\Component\PropertyAccess\PropertyAccessor;

use Behapi\Assert\Assert;

trait EachInCollectionTrait
{
    /** @var PropertyAccessor */
    private $accessor;

    abstract protected function getValue(?string $path);

    /**
     * @Then /^in the json, each elements in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should have a(?:n?) "(?P<property>(?:[^"]|\\")*)" property$/
     * @Then /^in the json, each elements in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) have a(?:n?) "(?P<property>(?:[^"]|\\")*)" property$/
     **/
    final public function the_json_path_elements_in_collection_should_have_a_property(string $path, ?string $not = null, string $property): void
    {
        $assert = Assert::that($this->getValue(empty($path) ? null : $path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert
            ->all()
            ->propertyExists($property)
        ;
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should be equal to "(?P<expected>(?:[^"]|\\")*)"$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) be equal to "(?P<expected>(?:[^"]|\\")*)"$/
     */
    final public function the_json_each_elements_in_collection_should_be_equal_to(string $path, string $property, ?string $not = null, string $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);

        Assert::that($values)
            ->isTraversable()
        ;

        $values = array_map(function ($value) use ($property) { return $this->accessor->getValue($value, $property); }, $values);

        $assert = Assert::that($values)
            ->all()
        ;

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->same($expected);
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should be (?P<expected>true|false)$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) be (?P<expected>true|false)$/
     */
    final public function the_json_each_elements_in_collection_should_be_bool(string $path, string $property, ?string $not = null, string $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);

        Assert::that($values)
            ->isTraversable()
        ;

        $values = array_map(function ($value) use ($property) { return $this->accessor->getValue($value, $property); }, $values);

        $assert = Assert::that($values)
            ->all()
            ->boolean()
        ;

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->same($expected === 'true');
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should be equal to (?P<expected>[0-9]+)$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) be equal to (?P<expected>[0-9]+)$/
     */
    final public function the_json_each_elements_in_collection_should_be_equal_to_int(string $path, string $property, ?string $not = null, int $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);

        Assert::that($values)
            ->isTraversable()
        ;

        $values = array_map(function ($value) use ($property) { return $this->accessor->getValue($value, $property); }, $values);

        $assert = Assert::that($values)
            ->all()
            ->integer()
        ;

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->same($expected);
    }

    /**
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should contain "(?P<expected>(?:[^"]|\\")*)"$/
     * @Then /^in the json, each "(?P<property>(?:[^"]|\\")*)" property in the (?:root|\"(?P<path>(?:[^"]|\\")*)\") collection should (?P<not>not) contain "(?P<expected>(?:[^"]|\\")*)"$/
     **/
    final public function the_json_each_elements_in_collection_should_contain(string $path, string $property, ?string $not = null, string $expected): void
    {
        $values = $this->getValue(empty($path) ? null : $path);

        Assert::that($values)
            ->isTraversable()
        ;

        $values = array_map(function ($value) use ($property) { return $this->accessor->getValue($value, $property); }, $values);

        $assert = Assert::that($values)
            ->all()
        ;

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->contains($expected);
    }
}
