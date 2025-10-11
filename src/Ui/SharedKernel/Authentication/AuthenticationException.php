<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

use Throwable;
use RuntimeException;

class AuthenticationException extends RuntimeException
{
    public static function create(Throwable $previousException = null): self
    {
        return new self('Authentication required to access this resource', 401, $previousException);
    }
}
