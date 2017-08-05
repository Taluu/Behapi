<?php
namespace Behapi\Context;

use stdClass;

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
    protected function getJson(): stdClass
    {
        return json_decode((string) $this->getResponse()->getBody());
    }

    public function responseIsValidjson()
    {
        Assert::same($this->getResponse()->getHeaderLine('Content-Type'), 'application/json', 'The response should have a valid content-type (expected %2$s, got %1$s)');

        parent::responseIsValidjson();
    }
}
