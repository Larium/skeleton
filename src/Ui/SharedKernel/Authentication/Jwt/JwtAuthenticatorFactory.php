<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication\Jwt;

use Firebase\JWT\Key;
use InvalidArgumentException;

class JwtAuthenticatorFactory
{
    public const HS256 = 'HS256';

    public const RS256 = 'RS256';

    public function __construct(
        private readonly string $algorithm,
        private readonly string $hs256Key = '',
        private readonly string $rs256PublicKey = '',
    ) {

    }

    public function create(): JwtAuthenticator
    {
        $key = match($this->algorithm) {
            self::HS256 => $this->createKeyForHs256(),
            self::RS256 => $this->createKeyForRs256(),
        };

        return new JwtAuthenticator($key);
    }

    private function createKeyForHs256(): Key
    {
        if (empty($this->hs256Key)) {
            throw new InvalidArgumentException('JWT_HS256_KEY env variable is missing');
        }

        return new Key($this->hs256Key, self::HS256);
    }

    private function createKeyForRs256(): Key
    {
        if (empty($this->rs256PublicKey)) {
            throw new InvalidArgumentException('JWT_RS256_PUBLIC_KEY_PATH env variable is missing');
        }

        return new Key($this->rs256PublicKey, self::RS256);
    }
}
