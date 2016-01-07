<?php
namespace Wisembly\Behat\Context;

use DatetimeImmutable;
use InvalidArgumentException;

use PHPUnit_Framework_Assert as Assert;

use Wisembly\Behat\Extension\Context\ApiTrait;
use Wisembly\Behat\Extension\Context\ApiInterface;

class Json extends AbstractJson implements ApiInterface
{
    use ApiTrait;

    /** {@inheritDoc} */
    protected function getJson()
    {
        return $this->getResponse()->json(['object' => true]);
    }

    public function responseIsValidjson()
    {
        Assert::assertSame('application/json', $this->getResponse()->getHeader('Content-Type', false), 'The response should have a valid content-type');

        parent::responseIsValidjson();
    }

    /** @Then in the json, the date difference between :from and :to should be equal to :diff day(s) */
    public function theDateDiffShouldBeEqualTo($from, $to, $diff, $format = 'days')
    {
        $to = new DatetimeImmutable($this->getValue($to));
        $from = new DatetimeImmutable($this->getValue($from));

        switch ($format) {
            case 'days':
                $format = '%a';
                break;

            case 'hours':
                $format = '%h';
                break;

            case 'minutes':
                $format = '%i';
                break;

            case 'seconds':
                $format = '%s';
                break;

            default:
                throw new InvalidArgumentException(sprintf('Format %s not recognized', $format));
        }

        $interval = $to->diff($from, true);

        Assert::assertEquals($diff, $interval->format($format));
    }

    /** @Then in the json, :path should be a valid json encoded string */
    public function theJsonPathShouldBeAValidJsonEncodedString($path)
    {
        $value = json_decode($this->getValue($path));

        Assert::assertNotNull($value);
        Assert::assertSame(JSON_ERROR_NONE, json_last_error());
    }
}
