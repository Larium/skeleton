<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\SharedKey;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Larium\Ui\SharedKernel\Authentication\Credential;
use Larium\Ui\SharedKernel\Authentication\CredentialCollector;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;

class SharedKeyCredentialCollector implements CredentialCollector
{
    public function __construct(
        private readonly string $sharedKeyName
    ) {
        if (empty($sharedKeyName)) {
            throw new InvalidArgumentException('SHARED_KEY_NAME env variable is missing');
        }
    }

    public function collect(ServerRequestInterface $request): Credential
    {
        $authentication = $request->getHeaderLine($this->sharedKeyName);

        if (empty($authentication)) {
            throw AuthenticationException::create();
        }

        return new Credential('', $authentication);
    }
}
