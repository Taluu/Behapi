<?php
namespace Behapi\Context;

use stdClass;

use Behapi\Extension\Context\ApiTrait;
use Behapi\Extension\Context\ApiInterface;

use Behapi\Extension\Tools\LastHistory;

use Webmozart\Assert\Assert;

class Json extends AbstractJson implements ApiInterface
{
    use ApiTrait;

    public function __construct(LastHistory $history)
    {
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

