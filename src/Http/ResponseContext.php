<?php declare(strict_types=1);
namespace Behapi\Http;

use RuntimeException;

use Behat\Behat\Context\Context;

use Webmozart\Assert\Assert;

use Behapi\HttpHistory\History as HttpHistory;

final class ResponseContext implements Context
{
    use Response;

    public function __construct(HttpHistory $history)
    {
        $this->history = $history;
    }

    /** @Then the status code should be :expected */
    public function status_code_should_be(int $expected): void
    {
        $response = $this->getResponse();
        Assert::same($response->getStatusCode(), $expected);
    }

    /** @Then the status code should not be :expected */
    public function status_code_should_not_be(int $expected): void
    {
        $response = $this->getResponse();
        Assert::notSame($response->getStatusCode(), $expected);
    }

    /** @Then the content-type should be equal to :expected */
    public function content_type_should_be(string $expected): void
    {
        $response = $this->getResponse();
        Assert::same($response->getHeaderLine('Content-type'), $expected);
    }

    /** @Then the response header :header should be equal to :expected */
    public function header_should_be(string $header, string $expected): void
    {
        $response = $this->getResponse();
        Assert::same($response->getHeaderLine($header), $expected);
    }

    /** @Then the response header :header should contain :expected */
    public function header_should_contain(string $header, string $expected): void
    {
        $response = $this->getResponse();
        Assert::contains($response->getHeaderLine($header), $expected);
    }

    /** @Then the response should have a header :header */
    public function response_should_have_header(string $header): void
    {
        $response = $this->getResponse();
        Assert::true($response->hasHeader($header));
    }

    /** @Then the response should have sent some data */
    public function response_should_have_sent_some_data(): void
    {
        $body = $this->getResponse()->getBody();

        Assert::notNull($body->getSize());
        Assert::greaterThan($body->getSize(), 0);
    }

    /** @Then the response should not have sent any data */
    public function response_should_not_have_any_data(): void
    {
        $body = $this->getResponse()->getBody();
        Assert::nullOrSame($body->getSize(), 0);
    }

    /** @Then the response should contain :data */
    public function response_should_contain(string $data): void
    {
        $response = $this->getResponse();
        Assert::contains((string) $response->getBody(), $data);
    }

    /** @Then the response should not contain :data */
    public function response_should_not_contain(string $data): void
    {
        $response = $this->getResponse();
        Assert::notContains((string) $response->getBody(), $data);
    }

    /** @Then the response should be :data */
    public function response_should_be(string $data): void
    {
        $response = $this->getResponse();
        Assert::eq((string) $response->getBody(), $data);
    }

    /** @Then the response should not be :data */
    public function response_should_not_be(string $data): void
    {
        $response = $this->getResponse();
        Assert::notEq((string) $response->getBody(), $data);
    }

}
