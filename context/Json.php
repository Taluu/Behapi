<?php
namespace Behapi\Context;

use stdClass;

use PHPUnit_Framework_Assert as Assert;

use Behapi\Extension\Context\ApiTrait;
use Behapi\Extension\Context\ApiInterface;

class Json extends AbstractJson implements ApiInterface
{
    use ApiTrait;

    /** {@inheritDoc} */
    protected function getJson(): stdClass
    {
        return $this->getResponse()->json(['object' => true]);
    }

    public function responseIsValidjson()
    {
        Assert::assertSame('application/json', $this->getResponse()->getHeader('Content-Type', false), 'The response should have a valid content-type');

        parent::responseIsValidjson();
    }
}
