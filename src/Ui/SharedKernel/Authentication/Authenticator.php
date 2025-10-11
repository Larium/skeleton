<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

interface Authenticator
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(Credential $credentials): AuthenticationResult;
}
