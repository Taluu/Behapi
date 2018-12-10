<?php declare(strict_types=1);
namespace Behapi\Json;

use Assert\Assert;

trait ComparisonTrait
{
    abstract protected function getValue(?string $path);

    /** @Then in the json, :path should be greater than :expected */
    final public function the_json_path_should_be_greater_than(string $path, int $expected): void
    {
        Assert::that($this->getValue($path))
            ->greaterThan($expected)
        ;
    }

    /** @Then in the json, :path should be greater than or equal to :expected */
    final public function the_json_path_should_be_greater_or_equal_than(string $path, int $expected): void
    {
        Assert::that($this->getValue($path))
            ->greaterOrEqualThan($expected)
        ;
    }

    /** @Then in the json, :path should be less than :expected */
    final public function the_json_path_should_be_less_than(string $path, int $expected): void
    {
        Assert::that($this->getValue($path))
            ->lessThan($expected)
        ;
    }

    /** @Then in the json, :path should be less than or equal to :expected */
    final public function the_json_path_should_be_less_or_equal_than(string $path, int $expected): void
    {
        Assert::that($this->getValue($path))
            ->lessOrEqualThan($expected)
        ;
    }
}
