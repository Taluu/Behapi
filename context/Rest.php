<?php
namespace Behapi\Context;

use RuntimeException;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

use PHPUnit_Framework_Assert as Assert;

use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Message\RequestInterface as GuzzleRequest;

use Behapi\Extension\Context\ApiTrait;
use Behapi\Extension\Context\ApiInterface;
use Behapi\Extension\Context\TwigInterface;
use Behapi\Extension\Context\TwigTrait;

class Rest implements ApiInterface, Context, TwigInterface
{
    use ApiTrait;
    use TwigTrait;

    /** @var GuzzleRequest|null */
    private $request;

    /** @When /^I create a "(?P<method>GET|POST|PATCH|PUT|DELETE)" request to "(?P<url>.+?)"$/ */
    public function createARequest(string $method, string $url)
    {
        $url = trim($url);

        if ('/' === $url[0]) {
            $url = substr($url, 1);
        }

        $client = $this->getClient();
        $history = $this->getHistory();

        $history->clear();
        $this->request = $client->createRequest(strtoupper($method), $url);

        // let's set a default content-type
        $this->setContentType($this->getDefaultContentType());
    }

    /** @When I add/set the value :value to the parameter :parameter */
    public function addAParameter(string $parameter, string $value)
    {
        $query = $this->getRequest()->getQuery();
        $query->add($parameter, $value);
    }

    /** @When I set the following query arguments: */
    public function setTheParameters(TableNode $parameters)
    {
        $request = $this->getRequest();
        $query = $request->getQuery();
        $query->clear();

        foreach ($parameters->getRowsHash() as $parameter => $value) {
            if (is_string($value)) {
                $value = $this->renderString($value);
            }
            $query->add($parameter, $value);
        }
    }

    /** @When I set the content-type to :type */
    public function setContentType(string $type)
    {
        $request = $this->getRequest();
        $request->setHeader('Content-Type', $type);
    }

    /** @When I set the following body: */
    public function setTheBody(string $body)
    {
        $request = $this->getRequest();
        $request->setBody(Stream::factory($this->renderString($body)));
    }

    /** @When I add/set the value :value to the header :header */
    public function addHeader(string $header, string $value)
    {
        $request = $this->getRequest();
        $request->addHeader($header, $value);
    }

    /** @When I set the headers: */
    public function setHeaders(TableNode $headers)
    {
        $request = $this->getRequest();
        $request->setHeaders($headers->getRowsHash());
    }

    /** @When I send the request */
    public function sendRequest()
    {
        $client = $this->getClient();
        $request = $this->getRequest();

        $this->response = $client->send($request);
    }

    /** @Then the status code should be :expected */
    public function statusCodeShouldBe(int $expected)
    {
        $response = $this->getResponse();
        Assert::assertSame($expected, (int) $response->getStatusCode());
    }

    /** @Then the status code should not be :expected */
    public function statusCodeShouldNotBe(int $expected)
    {
        $response = $this->getResponse();
        Assert::assertNotSame($expected, (int) $response->getStatusCode());
    }

    /** @Then the content-type should be equal to :expected */
    public function contentTypeShouldBe(string $expected)
    {
        $response = $this->getResponse();
        Assert::assertSame($expected, $response->getHeader('Content-type'));
    }

    /** @Then the response header :header should be equal to :expected */
    public function headerShouldBe(string $header, string $expected)
    {
        $response = $this->getResponse();
        Assert::assertSame($expected, $response->getHeader($header));
    }

    /** @Then the response header :header should contain :expected */
    public function headerShouldContain(string $header, string $expected)
    {
        $response = $this->getResponse();
        Assert::assertContains($expected, (string) $response->getHeader($header));
    }

    /** @Then the response should have a header :header */
    public function responseShouldHaveHeader(string $header)
    {
        $response = $this->getResponse();
        Assert::assertTrue($response->hasHeader($header));
    }

    /** @Then the response should have sent some data */
    public function responseShouldHaveSentSomeData()
    {
        $response = $this->getResponse();
        Assert::assertNotNull($response->getBody());
    }

    /** @Then the response should not have sent any data */
    public function responseShouldNotHaveAnyData()
    {
        $response = $this->getResponse();
        Assert::assertNull($response->getBody());
    }

    /** @Then the response should contain :data */
    public function responseShouldContain(string $data)
    {
        $response = $this->getResponse();
        Assert::assertContains($data, (string) $response->getBody());
    }

    /** @Then the response should not contain :data */
    public function responseShouldNotContain(string $data)
    {
        $response = $this->getResponse();
        Assert::assertNotContains($data, (string) $response->getBody());
    }

    /** @Then the response should be :data */
    public function responseShouldBe(string $data)
    {
        $response = $this->getResponse();
        Assert::assertEquals($data, (string) $response->getBody());
    }

    /** @Then the response should not be :data */
    public function responseShouldNotBe(string $data)
    {
        $response = $this->getResponse();
        Assert::assertNotEquals($data, (string) $response->getBody());
    }

    /**
     * @AfterScenario @api
     * @AfterScenario @rest
     */
    public function clearCache()
    {
        $this->request = null;
    }

    /**
     * @return GuzzleRequest
     * @throws RuntimeException
     */
    public function getRequest(): GuzzleRequest
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

