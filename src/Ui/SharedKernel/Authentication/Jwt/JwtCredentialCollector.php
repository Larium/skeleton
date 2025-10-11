<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\Jwt;

use Psr\Http\Message\ServerRequestInterface;
use Larium\Ui\SharedKernel\Authentication\Credential;
use Larium\Ui\SharedKernel\Authentication\CredentialCollector;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;

use function substr;

class JwtCredentialCollector implements CredentialCollector
{
    public function collect(ServerRequestInterface $request): Credential
    {
        $authorizationHeader = $request->getHeaderLine('Authorization');

        if (empty($authorizationHeader)) {
            throw AuthenticationException::create();
        }

        $bearer = substr($authorizationHeader, 0, 6);

        if (!empty($bearer) && strtolower($bearer) === 'bearer') {
            $authorizationHeader = substr($authorizationHeader, 7);
        }

        return new Credential('', $authorizationHeader);
    }
}
