<?php declare(strict_types=1);
namespace Behapi\PhpMatcher;

use InvalidArgumentException;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Behapi\Context\ApiTrait;
use Behapi\HttpHistory as HttpHistory;

use function sprintf;
use function json_encode;
use function json_decode;

class JsonContext implements Context
{
    use ApiTrait;

    /** @var BehapiFactory */
    private $factory;

    /** @var PropertyAccessor */
    private $accessor;

    public function __construct(HttpHistory $history)
    {
        $this->history = $history;
        $this->factory = new MatcherFactory;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /** @Then the root should match: */
    public function root_should_match(PyStringNode $pattern)
    {
        $matcher = $this->factory->createMatcher();

        if ($matcher->match($this->getJson(), $pattern->getRaw())) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'The json root does not match with the given pattern (error : %s)',
                $matcher->getError()
            )
        );
    }

    /** @Then in the json, :path should match: */
    public function path_should_match(string $path, PyStringNode $pattern)
    {
        $value = $this->getValue($path);
        $matcher = $this->factory->createMatcher();

        $json = json_encode($value);

        if ($matcher->match($json, $pattern->getRaw())) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'The json path "%s" does not match with the given pattern (error : %s)',
                $path,
                $matcher->getError()
            )
        );
    }

    /** @Then in the json, :path should not match: */
    public function path_should_not_match(string $path, PyStringNode $pattern)
    {
        $value = $this->getValue($path);
        $matcher = $this->factory->createMatcher();

        $json = json_encode($value);

        if (!$matcher->match($json, $pattern->getRaw())) {
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'The json path "%s" matches with the given pattern (error : %s)',
                $path,
                $matcher->getError()
            )
        );
    }

    private function getJson(): ?stdClass
    {
        return json_decode((string) $this->getResponse()->getBody());
    }

    private function getValue(string $path)
    {
        $json = $this->getJson();

        if (null === $json) {
            throw new InvalidArgumentException('Expected a Json valid content, got none');
        }

        return $this->accessor->getValue($json, $path);
    }
}
