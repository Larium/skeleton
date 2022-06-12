<?php

declare(strict_types=1);

namespace Larium\App\Shared\Infrastructure\Http\Middleware;

use ErrorException;
use Larium\App\Shared\Infrastructure\Http\Responder\HtmlResponder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    private HtmlResponder $responder;

    public function __construct(
        HtmlResponder $responder,
        LoggerInterface $logger
    ) {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        $this->responder = $responder;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $this->logger->error($e->__toString());
            return $this->responder->getResponse(500, 'error/500.html.twig', [], $e);
        }
    }
}
