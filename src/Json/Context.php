<?php declare(strict_types=1);
namespace Behapi\Json;

use DateTimeImmutable;

use Throwable;
use InvalidArgumentException;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context as BehatContext;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Behapi\Assert\Assert;
use Behapi\HttpHistory\History as HttpHistory;

use function sprintf;

use function json_decode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

class Context implements BehatContext
{
    use CountTrait;
    use ComparisonTrait;
    use EachInCollectionTrait;

    /** @var HttpHistory */
    private $history;

    /** @var string[] */
    private $contentTypes;

    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(HttpHistory $history, array $contentTypes = ['application/json'])
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $this->history = $history;
        $this->contentTypes = $contentTypes;
    }

    /** @return mixed */
    protected function getValue(?string $path)
    {
        $json = json_decode((string) $this->history->getLastResponse()->getBody());

        return $path === null ? $json : $this->accessor->getValue($json, $path);
    }

    /**
     * @Then :path should be accessible in the latest json response
     * @Then :path should :not exist in the latest json response
     */
    final public function path_should_be_readable(string $path, ?string $not = null): void
    {
        $assert = Assert::that($this->accessor->isReadable($this->getValue(null), $path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->same(true);
    }

    /**
     * @Then in the json, :path should be equal to :expected
     * @Then in the json, :path should :not be equal to :expected
     */
    final public function the_json_path_should_be_equal_to(string $path, ?string $not = null, $expected): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->eq($expected);
    }

    /**
     * @Then in the json, :path should be:
     * @Then in the json, :path should :not be:
     */
    final public function the_json_path_should_be_py_string(string $path, ?string $not = null, PyStringNode $expected): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->same($expected->getRaw());
    }

    /**
     * @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should be (?P<expected>true|false)$/
     * @Then /^in the json, "(?P<path>(?:[^"]|\\")*)" should :not be (?P<expected>true|false)$/
     */
    final public function the_json_path_should_be(string $path, ?string $not = null, string $expected): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->same($expected === 'true');
    }

    /**
     * @Then in the json, :path should be null
     * @Then in the json, :path should :not be null
     */
    final public function the_json_path_should_be_null(string $path, ?string $not = null): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->null();
    }

    /**
     * @Then in the json, :path should be empty
     * @Then in the json, :path should :not be empty
     */
    final public function the_json_path_should_be_empty(string $path, ?string $not = null): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->empty();
    }

    /**
     * @Then in the json, :path should contain :expected
     * @Then in the json, :path should :not contain :expected
     */
    final public function the_json_path_contains(string $path, ?string $not = null, $expected): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->contains($expected);
    }

    /** @Then in the json, :path collection should contain an element with :value equal to :expected */
    final public function the_json_path_collection_contains(string $path, string $value, $expected): void
    {
        $collection = $this->getValue($path);

        Assert::that($collection)
            ->isTraversable()
        ;

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
            new DateTimeImmutable($this->getValue($path));
        } catch (Throwable $t) {
            throw new InvalidArgumentException("$path does not contain a valid date");
        }
    }

    /**
     * @Then in the json, the root should be an array
     * @Then in the json, :path should be an array
     */
    final public function should_be_an_array(?string $path = null): void
    {
        Assert::that($this->getValue($path))
            ->isArray()
        ;
    }

    /** @Then in the json, :path should be a valid json encoded string */
    final public function the_json_path_should_be_a_valid_json_encoded_string(string $path): void
    {
        Assert::that($this->getValue($path))
            ->isJsonString()
        ;
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
    public function response_should_be_a_valid_json_response()
    {
        $this->getValue(null);

        [$contentType,] = explode(';', $this->history->getLastResponse()->getHeaderLine('Content-Type'), 2);

        Assert::that(json_last_error())
            ->same(JSON_ERROR_NONE, sprintf('The response is not a valid json (%s)', json_last_error_msg()))
        ;

        Assert::that($contentType)
            ->choice($this->contentTypes, 'The response should have a valid content-type (expected one of %2$s, got %1$s)')
        ;
    }

    /**
     *  @Then in the json, :path should match :pattern
     *  @Then in the json, :path should :not match :pattern
     */
    final public function the_json_path_should_match(string $path, ?string $not = null, string $pattern): void
    {
        $assert = Assert::that($this->getValue($path));

        if ($not !== null) {
            $assert = $assert->not();
        }

        $assert->regex($pattern);
    }
}
