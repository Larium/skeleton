<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Middleware;

use Throwable;
use ErrorException;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Larium\Ui\Api\Responder\JsonResponder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Larium\Ui\SharedKernel\Error\ExceptionErrorMapper;

final class ExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JsonResponder $responder,
        private readonly LoggerInterface $logger,
        private readonly ExceptionErrorMapper $errorMapper
    ) {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            ['status' => $status, 'payload' => $payload] = $this->errorMapper->map($e);
            if ($status >= 500) {
                $this->logger->error($e->__toString());
            }
            return $this->responder->getResponse($payload, $status);
        }
    }
}
