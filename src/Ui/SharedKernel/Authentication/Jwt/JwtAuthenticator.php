<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\Jwt;

use Firebase\JWT\Key;
use Larium\Ui\SharedKernel\Authentication\Credential;
use Larium\Ui\SharedKernel\Authentication\Authenticator;
use Larium\Ui\SharedKernel\Authentication\AuthenticationResult;

class JwtAuthenticator implements Authenticator
{
    public function __construct(
        private readonly Key $key
    ) {
    }

    public function authenticate(Credential $credentials): AuthenticationResult
    {
        $service = new JwtService();

        $payload = $service->decode($credentials->getSecret(), $this->key);

        return new AuthenticationResult($payload);
    }
}
