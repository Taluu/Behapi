<?php declare(strict_types=1);
namespace Behapi\Http;

use RuntimeException;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

use Http\Discovery\Psr18ClientDiscovery;
use Http\Client\Common\PluginClientBuilder;

use function trim;
use function is_array;
use function http_build_query;

class RequestContext implements Context
{
    /** @var ?RequestInterface */
    private $request;

    /** @var array<string, mixed> Query args to add */
    private $query = [];

    /** @var ClientInterface */
    private $client;

    /** @var PluginClientBuilder */
    private $builder;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    public function __construct(PluginClientBuilder $builder, StreamFactoryInterface $streamFactory, RequestFactoryInterface $requestFactory)
    {
        $this->builder = $builder;
        $this->streamFactory = $streamFactory;
        $this->requestFactory = $requestFactory;

        $this->client = Psr18ClientDiscovery::find();
    }

    /** @When /^I create a "(?P<method>GET|POST|PATCH|PUT|DELETE|OPTIONS|HEAD)" request to "(?P<url>.+?)"$/ */
    final public function create_a_request(string $method, string $url): void
    {
        $url = trim($url);

        $this->query = [];
        $this->request = $this->requestFactory->createRequest(strtoupper($method), $url);

        // let's set a default content-type
        $this->set_the_content_type($this->getDefaultContentType());
    }

    /**
     * @When /^I send a "(?P<method>GET|POST|PATCH|PUT|DELETE|OPTIONS|HEAD)" request to "(?P<url>.+?)"$/
     *
     * -------
     *
     * Shortcut for `When I create a X request to Then send the request`
     */
    final public function send_a_request(string $method, string $url): void
    {
        $this->create_a_request($method, $url);
        $this->send_request();
    }

    /**
     * @param mixed $value
     * @When I add/set the value :value to the parameter :parameter
     */
    final public function add_a_parameter(string $parameter, $value): void
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
    final public function set_the_parameters(TableNode $parameters): void
    {
        $this->query = [];

        foreach ($parameters->getRowsHash() as $parameter => $value) {
            $this->add_a_parameter($parameter, $value);
        }
    }

    /** @When I set the content-type to :type */
    final public function set_the_content_type(string $type): void
    {
        $request = $this->getRequest();
        $this->request = $request->withHeader('Content-Type', $type);
    }

    /** @When I set the following body: */
    final public function set_the_body(string $body): void
    {
        $stream = $this->streamFactory->createStream($body);

        $request = $this->getRequest();
        $this->request = $request->withBody($stream);
    }

    /** @When I add/set the value :value to the header :header */
    final public function add_header(string $header, string $value): void
    {
        $request = $this->getRequest();
        $this->request = $request->withAddedHeader($header, $value);
    }

    /** @When I set the headers: */
    final public function set_headers(TableNode $headers): void
    {
        $request = $this->getRequest();

        foreach ($headers->getRowsHash() as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        $this->request = $request;
    }

    /** @When I send the request */
    final public function send_request(): void
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

        $client = $this->builder->createClient($this->client);
        $client->sendRequest($request);
    }

    /** @AfterScenario @api */
    final public function clearCache(): void
    {
        $this->query = [];
        $this->request = null;
    }

    final public function getRequest(): RequestInterface
    {
        if (null === $this->request) {
            throw new RuntimeException('No request initiated');
        }

        return $this->request;
    }

    protected function getDefaultContentType(): string
    {
        return 'application/json';
    }
}
