<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection\Response;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

use Behapi\Debug\Introspection\Adapter;
use Behapi\Debug\Introspection\UnsupportedMessage;

final class EchoerAdapter implements Adapter
{
    // 1 - key
    // 2 - value
    private const TEMPLATE = "\033[36m| \033[1m%s : \033[0;36m%s\033[0m\n";

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

        echo "\n";

        printf(self::TEMPLATE, 'Response status', "{$message->getStatusCode()} {$message->getReasonPhrase()}");

        foreach ($this->headers as $header) {
            printf(self::TEMPLATE, "Response {$header}", $message->getHeaderLine($header));
        }

        $body = (string) $message->getBody();

        if (!empty($body)) {
            echo "\n{$body}";
        }

        echo "\n";
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ResponseInterface;
    }
}
