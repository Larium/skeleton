<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

use Psr\Http\Message\ServerRequestInterface;

interface CredentialCollector
{
    /**
     * @throws AuthenticationException
     */
    public function collect(ServerRequestInterface $request): Credential;
}
