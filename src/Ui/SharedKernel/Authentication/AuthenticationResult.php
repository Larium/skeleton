<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

use JsonSerializable;
use InvalidArgumentException;

class AuthenticationResult implements JsonSerializable
{
    public function __construct(
        private readonly array $payload
    ) {
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function jsonSerialize(): array
    {
        return $this->payload;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->payload)) {
            return $this->payload[$name];
        }

        throw new InvalidArgumentException(sprintf('Undefined property: %s::%s', self::class, $name));
    }
}
