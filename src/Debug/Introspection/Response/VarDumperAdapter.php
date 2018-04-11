<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection\Response;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\VarDumper\VarDumper;

use Behapi\Debug\Introspection\Adapter;
use Behapi\Debug\Introspection\UnsupportedMessage;

final class VarDumperAdapter implements Adapter
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

        $introspect = [
            'Response Status' => "{$message->getStatusCode()} {$message->getReasonPhrase()}",
        ];

        foreach ($this->headers as $header) {
            $introspect["Response {$header}"] = $message->getHeaderLine($header);
        }

        $body = (string) $message->getBody();

        if (!empty($body)) {
            $introspect['Response Body'] = $body;
        }

        VarDumper::dump($introspect);
    }

    public function supports(MessageInterface $message): bool
    {
        return class_exists(VarDumper::class)
            && $message instanceof ResponseInterface
        ;
    }
}
