<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection\Request;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

use Symfony\Component\VarDumper\VarDumper;

use Behapi\Debug\Introspection\Adapter;
use Behapi\Debug\Introspection\UnsupportedMessage;

final class VarDumperAdapter implements Adapter
{
    public function introspect(MessageInterface $message, array $headers): void
    {
        if (!$this->supports($message)) {
            throw new UnsupportedMessage($message, RequestInterface::class);
        }

        // mandatory, clearing the line
        // todo : check how to clear without this echo...
        echo "\n";

        $introspect = [
            'Request' => "{$message->getMethod()} {$message->getUri()}",
        ];

        foreach ($headers as $header) {
            $introspect["Request {$header}"] = $message->getHeaderLine($header);
        }

        $body = (string) $message->getBody();

        if (!empty($body)) {
            $introspect['Request Body'] = $body;
        }

        VarDumper::dump($introspect);
    }

    public function supports(MessageInterface $message): bool
    {
        return class_exists(VarDumper::class)
            && $message instanceof RequestInterface
        ;
    }
}
