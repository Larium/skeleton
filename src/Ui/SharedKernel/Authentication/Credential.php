<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

class Credential
{
    public function __construct(
        private readonly string $identification,
        private readonly string $secret,
    ) {

    }

    public function getIdentification(): string
    {
        return $this->identification;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }
}
