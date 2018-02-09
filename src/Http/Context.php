<?php declare(strict_types=1);
namespace Behapi\Http;

use RuntimeException;

use Psr\Http\Message\RequestInterface;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Context as BehatContext;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Http\Client\HttpClient;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Behapi\EventListener\Events;
use Behapi\EventListener\RequestEvent;

use Behapi\HttpHistory\History as HttpHistory;

use function trim;
use function is_array;
use function http_build_query;

class Context implements BehatContext
{
    use Client;

    /** @var RequestInterface */
    private $request;

    /** @var mixed[] Query args to add */
    private $query;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(HttpClient $client, StreamFactory $streamFactory, MessageFactory $messageFactory, HttpHistory $history, EventDispatcherInterface $dispatcher)
    {
        $this->client = $client;
        $this->history = $history;
        $this->dispatcher = $dispatcher;
        $this->streamFactory = $streamFactory;
        $this->messageFactory = $messageFactory;
    }

    /** @When /^I create a "(?P<method>GET|POST|PATCH|PUT|DELETE|OPTIONS|HEAD)" request to "(?P<url>.+?)"$/ */
    public function createARequest(string $method, string $url)
    {
        $url = trim($url);

        $this->query = [];
        $this->request = $this->messageFactory->createRequest(strtoupper($method), $url);

        // let's set a default content-type
        $this->setContentType($this->getDefaultContentType());
    }

    /**
     * @When /^I send a "(?P<method>GET|POST|PATCH|PUT|DELETE|OPTIONS|HEAD)" request to "(?P<url>.+?)"$/
     *
     * -------
     *
     * Shortcut for `When I create a X request to Then send the request`
     */
    public function sendARequest($method, $url)
    {
        $this->createARequest($method, $url);
        $this->sendRequest();
    }

    /** @When I add/set the value :value to the parameter :parameter */
    public function addAParameter(string $parameter, string $value)
    {
        if (!isset($this->query[$parameter])) {
            $this->query[$parameter] = $value;
            return;
        }

        $current = &$this->query[$parameter];

        if (is_array($current)) {
            $current[] = $value;
            return;
        }

        $current = [$current, $value];
    }

    /** @When I set the following query arguments: */
    public function setTheParameters(TableNode $parameters)
    {
        $this->query = [];

        foreach ($parameters->getRowsHash() as $parameter => $value) {
            $this->addAParameter($parameter, $value);
        }
    }

    /** @When I set the content-type to :type */
    public function setContentType(string $type)
    {
        $request = $this->getRequest();
        $this->request = $request->withHeader('Content-Type', $type);
    }

    /** @When I set the following body: */
    public function setTheBody(string $body)
    {
        $stream = $this->streamFactory->createStream($body);

        $request = $this->getRequest();
        $this->request = $request->withBody($stream);
    }

    /** @When I add/set the value :value to the header :header */
    public function addHeader(string $header, string $value)
    {
        $request = $this->getRequest();
        $this->request = $request->withAddedHeader($header, $value);
    }

    /** @When I set the headers: */
    public function setHeaders(TableNode $headers)
    {
        $request = $this->getRequest();

        foreach ($headers->getRowsHash() as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $this->request = $request;
    }

    /** @When I send the request */
    public function sendRequest()
    {
        $request = $this->getRequest();

        if (!empty($this->query)) {
            $uri = $request->getUri();
            $current = $uri->getQuery();
            $query = http_build_query($this->query);

            if (!empty($current)) {
                $query = "{$current}&{$query}";
            }

            $uri = $uri->withQuery($query);
            $request = $request->withUri($uri);
        }

        $this->dispatcher->dispatch(Events::REQUEST_PRE_SENDING, $event = new RequestEvent($request));
        $this->client->sendRequest($event->getRequest());
    }

    /** @Then the status code should be :expected */
    public function statusCodeShouldBe(int $expected)
    {
        $response = $this->getResponse();
        Assert::same((int) $response->getStatusCode(), $expected);
    }

    /** @Then the status code should not be :expected */
    public function statusCodeShouldNotBe(int $expected)
    {
        $response = $this->getResponse();
        Assert::notSame((int) $response->getStatusCode(), $expected);
    }

    /** @Then the content-type should be equal to :expected */
    public function contentTypeShouldBe(string $expected)
    {
        $response = $this->getResponse();
        Assert::same($response->getHeaderLine('Content-type'), $expected);
    }

    /** @Then the response header :header should be equal to :expected */
    public function headerShouldBe(string $header, string $expected)
    {
        $response = $this->getResponse();
        Assert::same($response->getHeaderLine($header), $expected);
    }

    /** @Then the response header :header should contain :expected */
    public function headerShouldContain(string $header, string $expected)
    {
        $response = $this->getResponse();
        Assert::contains((string) $response->getHeaderLine($header), $expected);
    }

    /** @Then the response should have a header :header */
    public function responseShouldHaveHeader(string $header)
    {
        $response = $this->getResponse();
        Assert::true($response->hasHeader($header));
    }

    /** @Then the response should have sent some data */
    public function responseShouldHaveSentSomeData()
    {
        $body = $this->getResponse()->getBody();

        Assert::notNull($body->getSize());
        Assert::greaterThan($body->getSize(), 0);
    }

    /** @Then the response should not have sent any data */
    public function responseShouldNotHaveAnyData()
    {
        $body = $this->getResponse()->getBody();
        Assert::nullOrSame($body->getSize(), 0);
    }

    /** @Then the response should contain :data */
    public function responseShouldContain(string $data)
    {
        $response = $this->getResponse();
        Assert::contains((string) $response->getBody(), $data);
    }

    /** @Then the response should not contain :data */
    public function responseShouldNotContain(string $data)
    {
        $response = $this->getResponse();
        Assert::notContains((string) $response->getBody(), $data);
    }

    /** @Then the response should be :data */
    public function responseShouldBe(string $data)
    {
        $response = $this->getResponse();
        Assert::eq((string) $response->getBody(), $data);
    }

    /** @Then the response should not be :data */
    public function responseShouldNotBe(string $data)
    {
        $response = $this->getResponse();
        Assert::NotEq((string) $response->getBody(), $data);
    }

    /**
     * @AfterScenario @api
     * @AfterScenario @rest
     */
    public function clearCache()
    {
        $this->query = [];
        $this->request = null;
        // the history is resetted in its own event listener
    }

    /**
     * @return RequestInterface
     * @throws RuntimeException
     */
    public function getRequest(): RequestInterface
    {
        if (null === $this->request) {
            throw new RuntimeException('No request initiated');
        }

        return $this->request;
    }

    /**
     * Get the default content type, used when makeRequest is called
     *
     * @return string
     */
    protected function getDefaultContentType(): string
    {
        return 'application/json';
    }
}
