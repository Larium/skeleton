<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Error;

use Throwable;
use Larium\Framework\Contract\Routing\HttpNotFoundException;
use Larium\Framework\Contract\Routing\HttpMethodNotAllowedException;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;
use Larium\Ui\SharedKernel\Service\Validation\ValidationException;

final class ExceptionErrorMapper
{
    /**
     * Maps an exception to an HTTP status and standardized payload.
     *
     * @return array{status:int, payload:array{error:array{code:int,message:string,invalidParams?:array}}}
     */
    public function map(Throwable $e): array
    {
        if ($e instanceof AuthenticationException) {
            return [
                'status' => 401,
                'payload' => [
                    'error' => [
                        'code' => 401,
                        'message' => 'Invalid authorization'
                    ]
                ]
            ];
        }

        if ($e instanceof ValidationException) {
            return [
                'status' => 400,
                'payload' => [
                    'error' => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'invalidParams' => $e->getErrors(),
                    ]
                ]
            ];
        }

        if ($e instanceof HttpNotFoundException) {
            return [
                'status' => 404,
                'payload' => [
                    'error' => [
                        'code' => 404,
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        }

        if ($e instanceof HttpMethodNotAllowedException) {
            return [
                'status' => 405,
                'payload' => [
                    'error' => [
                        'code' => 405,
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        }

        $status = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
        return [
            'status' => $status,
            'payload' => [
                'error' => [
                    'code' => $status,
                    'message' => 'Unexpected Error'
                ]
            ]
        ];
    }
}
