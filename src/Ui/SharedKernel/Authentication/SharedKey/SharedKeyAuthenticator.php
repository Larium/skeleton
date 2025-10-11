<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\SharedKey;

use InvalidArgumentException;
use Larium\Ui\SharedKernel\Authentication\Credential;
use Larium\Ui\SharedKernel\Authentication\Authenticator;
use Larium\Ui\SharedKernel\Authentication\AuthenticationResult;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;

class SharedKeyAuthenticator implements Authenticator
{
    public function __construct(
        private readonly string $sharedKeyValue
    ) {
        if (empty($sharedKeyValue)) {
            throw new InvalidArgumentException('SHARED_KEY_VALUE env variable is missing');
        }
    }

    public function authenticate(Credential $credentials): AuthenticationResult
    {
        if (!hash_equals($this->sharedKeyValue, $credentials->getSecret())) {
            throw AuthenticationException::create();
        }

        return new AuthenticationResult([]);
    }
}
