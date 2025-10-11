<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;
use Firebase\JWT\SignatureInvalidException;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;

class JwtService
{
    public function decode(string $jwt, Key $key): array
    {
        try {
            return (array) JWT::decode($jwt, $key);
        } catch (SignatureInvalidException | UnexpectedValueException) {
            throw AuthenticationException::create();
        }
    }
}
