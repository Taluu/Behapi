<?php
namespace Behapi\Context;

use stdClass;
use InvalidArgumentException;

use Behapi\Context\ApiTrait;
use Behapi\Tools\HttpHistory;

use Webmozart\Assert\Assert;

class Json extends AbstractJson
{
    use ApiTrait;

    public function __construct(HttpHistory $history)
    {
        parent::__construct();
        $this->history = $history;
    }

    /** {@inheritDoc} */
    protected function getJson()
    {
        $this->responseIsValidjson();

        $decoded = json_decode((string) $this->getResponse()->getBody());

        Assert::same(JSON_ERROR_NONE, json_last_error(), sprintf('The response is not a valid json (%s)', json_last_error_msg()));
    }

    public function responseIsValidjson()
    {
        Assert::same($this->getResponse()->getHeaderLine('Content-Type'), 'application/json', 'The response should have a valid content-type (expected %2$s, got %1$s)');

        parent::responseIsValidjson();
    }
}
