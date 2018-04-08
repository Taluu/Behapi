<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection\Response;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

use Symfony\Component\VarDumper\VarDumper;

use Behapi\Debug\Introspection\Adapter;
use Behapi\Debug\Introspection\UnsupportedMessage;

final class VarDumperAdapter implements Adapter
{
    public function introspect(MessageInterface $message, array $headers): void
    {
        if (!$this->supports($message)) {
            throw new UnsupportedMessage($message, ResponseInterface::class);
        }

        // mandatory, clearing the line
        // todo : check how to clear without this echo...
        echo "\n";

        $introspect = [
            'Response Status' => "{$message->getStatusCode()} {$message->getReasonPhrase()}",
        ];

        foreach ($headers as $header) {
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
