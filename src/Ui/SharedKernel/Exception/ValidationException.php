<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Exception;

use InvalidArgumentException;

class ValidationException extends InvalidArgumentException
{
    private $errors = [];

    public static function badRequest(array $errors)
    {
        $e = new self(
            "Invalid attribute",
            400
        );

        $e->errors = $errors;

        return $e;
    }

    public function addError(string $message, string $attribute)
    {
        $this->errors[] = ['reason' => $message, 'name' => $attribute];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
