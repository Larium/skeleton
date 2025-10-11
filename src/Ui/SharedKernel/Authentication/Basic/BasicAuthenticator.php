<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\Basic;

use Larium\Ui\SharedKernel\Authentication\Credential;
use Larium\Ui\SharedKernel\Authentication\Authenticator;
use Larium\Ui\SharedKernel\Authentication\AuthenticationResult;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;

use function hash_equals;

class BasicAuthenticator implements Authenticator
{
    public function __construct(
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function authenticate(Credential $credentials): AuthenticationResult
    {
        if (hash_equals($this->username, $credentials->getIdentification())
            && hash_equals($this->password, $credentials->getSecret())
        ) {
            return new AuthenticationResult(['username' => $this->username]);
        }

        throw AuthenticationException::create();
    }
}
