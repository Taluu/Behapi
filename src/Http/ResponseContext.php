<?php declare(strict_types=1);
namespace Behapi\Http;

use RuntimeException;

use Behat\Behat\Context\Context;

// use Assert\Assert; // to be used when https://github.com/beberlei/assert/pull/264 is merged and released

use Behapi\Assert\Assert;
use Behapi\HttpHistory\History as HttpHistory;

final class ResponseContext implements Context
{
    /** @var HttpHistory */
    private $history;

    public function __construct(HttpHistory $history)
    {
        $this->history = $history;
    }

    /** @Then the status code should be :expected */
    public function status_code_should_be(int $expected): void
    {
        $response = $this->history->getLastResponse();

        Assert::that($response->getStatusCode())
            ->same($expected)
        ;
    }

    /** @Then the status code should not be :expected */
    public function status_code_should_not_be(int $expected): void
    {
        $response = $this->history->getLastResponse();

        Assert::that($response->getStatusCode())
            ->same($expected)
        ;
    }

    /** @Then the content-type should be equal to :expected */
    public function content_type_should_be(string $expected): void
    {
        $response = $this->history->getLastResponse();

        Assert::that($response->getHeaderLine('Content-type'))
            ->same($expected)
        ;
    }

    /** @Then the response header :header should be equal to :expected */
    public function header_should_be(string $header, string $expected): void
    {
        $response = $this->history->getLastResponse();

        Assert::that($response->getHeaderLine($header))
            ->same($expected)
        ;
    }

    /** @Then the response header :header should contain :expected */
    public function header_should_contain(string $header, string $expected): void
    {
        $response = $this->history->getLastResponse();

        Assert::that($response->getHeaderLine($header))
            ->same($expected)
        ;
    }

    /** @Then the response should have a header :header */
    public function response_should_have_header(string $header): void
    {
        $response = $this->history->getLastResponse();

        Assert::that($response->hasHeader($header))
            ->true()
        ;
    }

    /** @Then the response should have sent some data */
    public function response_should_have_sent_some_data(): void
    {
        $body = $this->history->getLastResponse()->getBody();

        Assert::that($body->getSize())
            ->notNull()
            ->greaterThan(0)
        ;
    }

    /** @Then the response should not have sent any data */
    public function response_should_not_have_any_data(): void
    {
        $body = $this->history->getLastResponse()->getBody();

        Assert::that($body->getSize())
            ->nullOr()
            ->same(0)
        ;
    }

    /** @Then the response should contain :data */
    public function response_should_contain(string $data): void
    {
        $response = $this->history->getLastResponse();

        Assert::that((string) $response->getBody())
            ->contains($data)
        ;
    }

    /** @Then the response should not contain :data */
    public function response_should_not_contain(string $data): void
    {
        $response = $this->history->getLastResponse();

        Assert::that((string) $response->getBody())
            ->notContains($data)
        ;
    }

    /** @Then the response should be :data */
    public function response_should_be(string $data): void
    {
        $response = $this->history->getLastResponse();

        Assert::that((string) $response->getBody())
            ->eq($data)
        ;
    }

    /** @Then the response should not be :data */
    public function response_should_not_be(string $data): void
    {
        $response = $this->history->getLastResponse();

        Assert::that((string) $response->getBody())
            ->notEq($data)
        ;
    }
}
