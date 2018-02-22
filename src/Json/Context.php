<?php declare(strict_types=1);
namespace Behapi\Json;

use stdClass;
use InvalidArgumentException;

use Webmozart\Assert\Assert;

use Behapi\Http\Response;
use Behapi\HttpHistory\History as HttpHistory;

use function sprintf;

use function json_decode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

class Context extends AbstractContext
{
    use Response;

    public function __construct(HttpHistory $history)
    {
        parent::__construct();
        $this->history = $history;
    }

    /** {@inheritDoc} */
    protected function getJson()
    {
        $decoded = json_decode((string) $this->getResponse()->getBody());

        Assert::same(JSON_ERROR_NONE, json_last_error(), sprintf('The response is not a valid json (%s)', json_last_error_msg()));

        return $decoded;
    }

    protected function getContentTypes(): array
    {
        return ['application/json'];
    }

    public function responseIsValidjson()
    {
        [$contentType,] = explode(';', $this->getResponse()->getHeaderLine('Content-Type'), 2);

        Assert::oneOf($contentType, $this->getContentTypes(), 'The response should have a valid content-type (expected one of %2$s, got %1$s)');

        parent::responseIsValidjson();
    }
}
