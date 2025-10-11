<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

use Psr\Http\Message\ServerRequestInterface;

class AuthenticatorService
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly CredentialCollector $credentialCollector
    ) {

    }

    /**
     * @throws AuthenticationException
     */
    public function __invoke(ServerRequestInterface $request): AuthenticationResult
    {
        $credentials = $this->credentialCollector->collect($request);

        return $this->authenticator->authenticate($credentials);
    }
}
