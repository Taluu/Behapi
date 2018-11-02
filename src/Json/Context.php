<?php declare(strict_types=1);
namespace Behapi\Json;

use stdClass;
use InvalidArgumentException;

use Webmozart\Assert\Assert;

use Behapi\HttpHistory\History as HttpHistory;

use function sprintf;

use function json_decode;
use function json_last_error;
use function json_last_error_msg;

use const JSON_ERROR_NONE;

class Context extends AbstractContext
{
    /** @var HttpHistory */
    private $history;

    public function __construct(HttpHistory $history)
    {
        parent::__construct();
        $this->history = $history;
    }

    protected function getJson()
    {
        return json_decode((string) $this->history->getLastResponse()->getBody());
    }

    protected function getContentTypes(): array
    {
        return ['application/json'];
    }

    /**
     * @Then the response should be a valid json response
     *
     * ---
     *
     * This method is built-on the default php's json extension. You should
     * overwrite it if you want to add supplementary checks or use something
     * else instead (such as Seldaek's JsonLint package).
     */
    public function response_should_be_a_valid_json_response()
    {
        $this->getJson();

        [$contentType,] = explode(';', $this->history->getLastResponse()->getHeaderLine('Content-Type'), 2);

        Assert::same(JSON_ERROR_NONE, json_last_error(), sprintf('The response is not a valid json (%s)', json_last_error_msg()));
        Assert::oneOf($contentType, $this->getContentTypes(), 'The response should have a valid content-type (expected one of %2$s, got %1$s)');
    }
}
