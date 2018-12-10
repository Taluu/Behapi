<?php declare(strict_types=1);
namespace Behapi\Json;

use Behapi\Assert\Assert;

trait CountTrait
{
    abstract protected function getValue(?string $path);

    /**
     * @Then in the json, the root collection should have at least :count element(s)
     * @Then in the json, :path collection should have at least :count element(s)
     */
    final public function the_json_collection_should_have_at_least_elements(?string $path = null, int $count): void
    {
        Assert::that($this->getValue($path))
            ->isCountable()
            ->minCount($count)
        ;
    }

    /**
     * @Then in the json, the root collection should have :count element(s)
     * @Then in the json, :path collection should have :count element(s)
     */
    final public function the_json_path_should_have_elements(?string $path = null, int $count): void
    {
        Assert::that($this->getValue($path))
            ->isCountable()
            ->count($count)
        ;
    }

    /**
     * @Then in the json, the root collection should have at most :count element(s)
     * @Then in the json, :path collection should have at most :count element(s)
     */
    final public function the_json_path_should_have_at_most_elements(?string $path = null, int $count): void
    {
        Assert::that($this->getValue($path))
            ->isCountable()
            ->maxCount($count)
        ;
    }
}
