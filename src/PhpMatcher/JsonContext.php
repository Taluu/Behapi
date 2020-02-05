<?php declare(strict_types=1);
namespace Behapi\PhpMatcher;

use InvalidArgumentException;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

use Behapi\HttpHistory\History as HttpHistory;

use function sprintf;
use function is_string;

class JsonContext implements Context
{
    /** @var HttpHistory */
    private $history;

    /** @var MatcherFactory */
    private $factory;

    public function __construct(HttpHistory $history)
    {
        $this->history = $history;
        $this->factory = new MatcherFactory;
    }

    /** @Then the root should match: */
    final public function root_should_match(PyStringNode $pattern): void
    {
        $matcher = $this->factory->createMatcher();

        if ($matcher->match((string) $this->history->getLastResponse()->getBody(), $pattern->getRaw())) {
            return;
        }

        $error = $matcher->getError();
        assert(is_string($error));

        throw new InvalidArgumentException(
            sprintf(
                'The json root does not match with the given pattern (error : %s)',
                $error
            )
        );
    }
}
