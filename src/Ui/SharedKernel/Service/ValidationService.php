<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Service;

use Larium\Ui\SharedKernel\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidationService
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    public function validate(object $object, array $context = []): void
    {
        $errors = $this->validator->validate($object, null, $context);
        if ($errors->count() !== 0) {
            $invalidParams = [];
            foreach ($errors as $error) {
                $invalidParams[] = ['reason' => $error->getMessage(), 'name' => $error->getPropertyPath()];
            }

            throw ValidationException::badRequest($invalidParams);
        }
    }
}
