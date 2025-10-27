<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Service\Validation;

use function json_decode;
use function json_last_error;
use function json_last_error_msg;

class JsonIntegrityValidation
{
    public function validate(string $content = null): array
    {
        $data = json_decode($content, true);
        if ($data === null) {
            $message = sprintf("Json validation: %s", (json_last_error_msg() ?? 'unknown parsing error'));
            throw new ValidationException($message, json_last_error());
        }

        return $data;
    }
}
