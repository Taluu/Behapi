<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection\Response\VarDumper;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\VarDumper\VarDumper;

use Behapi\Debug\Introspection\Adapter;
use Behapi\Debug\Introspection\UnsupportedMessage;

final class JsonAdapter implements Adapter
{
    /** @var iterable<string> */
    private $headers;

    /** @param iterable<string> $headers */
    public function __construct(iterable $headers)
    {
        $this->headers = $headers;
    }

    public function introspect(MessageInterface $message): void
    {
        if (!$this->supports($message)) {
            throw new UnsupportedMessage($message, ResponseInterface::class);
        }

        assert($message instanceof ResponseInterface);

        // mandatory, clearing the line
        // todo : check how to clear without this echo...
        echo "\n";

        $dump = [
            'Response Status' => "{$message->getStatusCode()} {$message->getReasonPhrase()}",
        ];

        foreach ($this->headers as $header) {
            $dump["Response {$header}"] = $message->getHeaderLine($header);
        }

        $body = (string) $message->getBody();

        if (!empty($body)) {
            $dump['Response Body'] = json_decode($body);
        }

        VarDumper::dump($dump);
    }

    public function supports(MessageInterface $message): bool
    {
        if (!class_exists(VarDumper::class)) {
            return false;
        }

        if (!$message instanceof ResponseInterface) {
            return false;
        }

        [$contentType,] = explode(';', $message->getHeaderLine('Content-Type'), 2);

        return 'application/json' === $contentType;
    }
}
