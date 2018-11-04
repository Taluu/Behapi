<?php declare(strict_types=1);
namespace Behapi\Debug\Introspection;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;

final class UnsupportedMessage extends InvalidArgumentException
{
    /** @var MessageInterface */
    private $httpMessage;

    public function __construct(MessageInterface $message, string $expected)
    {
        $this->httpMessage = $message;
        $type = get_class($message);

        parent::__construct("Wrong message type, expected {$expected}, got {$type}");
    }

    public function getHttpMessage(): MessageInterface
    {
        return $this->httpMessage;
    }
}
