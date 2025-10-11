<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\Basic;

use Psr\Http\Message\ServerRequestInterface;
use Larium\Ui\SharedKernel\Authentication\Credential;
use Larium\Ui\SharedKernel\Authentication\CredentialCollector;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;

use function explode;
use function preg_match;
use function base64_decode;

class BasicCredentialCollector implements CredentialCollector
{
    public function collect(ServerRequestInterface $request): Credential
    {
        $authentication = $request->getHeaderLine('Authorization');

        if (empty($authentication)) {
            throw AuthenticationException::create();
        }

        $result = preg_match("/^Basic (?<base64>(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?)$/", $authentication, $m);

        if ($result !== 1) {
            throw AuthenticationException::create();
        }

        list($username, $password) = explode(':', base64_decode($m['base64']));

        return new Credential($username, $password);
    }
}
