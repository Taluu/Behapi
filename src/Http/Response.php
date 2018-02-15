<?php declare(strict_types=1);
namespace Behapi\Http;

use RuntimeException;

use Psr\Http\Message\ResponseInterface;

use Http\Client\HttpClient;
use Http\Message\StreamFactory;
use Http\Message\MessageFactory;

use Behapi\HttpHistory\History as HttpHistory;

trait Response
{
    /** @var HttpHistory */
    private $history;

    public function getResponse(): ResponseInterface
    {
        $response = $this->history->getLastResponse();

        if (!$response instanceof ResponseInterface) {
            throw new RuntimeException('No response');
        }

        return $response;
    }
}
