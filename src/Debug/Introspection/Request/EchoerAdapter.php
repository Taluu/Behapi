<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection\Request;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

use Behapi\Debug\Introspection;
use Behapi\Debug\Introspection\Adapter;
use Behapi\Debug\Introspection\UnsupportedMessage;

final class EchoerAdapter implements Adapter
{
    // 1 - key
    // 2 - value
    private const TEMPLATE = "\033[36m| \033[1m%s : \033[0;36m%s\033[0m\n";

    public function introspect(MessageInterface $message, array $headers): void
    {
        if (!$this->supports($message)) {
            throw new UnsupportedMessage($message, RequestInterface::class);
        }

        assert($message instanceof RequestInterface);

        echo "\n";

        printf(self::TEMPLATE, 'Request', "{$message->getMethod()} {$message->getUri()}");

        foreach ($headers as $header) {
            printf(self::TEMPLATE, "Request {$header}", $message->getHeaderLine($header));
        }

        $body = (string) $message->getBody();

        if (!empty($body)) {
            echo "\n{$body}";
        }

        echo "\n";
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof RequestInterface;
    }
}
