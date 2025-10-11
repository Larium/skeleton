<?php

declare(strict_types=1);

namespace Larium\Ui\Web\Middleware;

use Throwable;
use ErrorException;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Larium\Ui\Web\Responder\HtmlResponder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Larium\Ui\SharedKernel\Exception\ValidationException;
use Larium\Framework\Contract\Routing\HttpNotFoundException;
use Larium\Ui\SharedKernel\Authentication\AuthenticationException;
use Larium\Framework\Contract\Routing\HttpMethodNotAllowedException;

class ExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HtmlResponder $htmlResponder
    ) {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (AuthenticationException $e) {
            $status = 401;
            $payload = [
                'error' => [
                    'code' => $status,
                    'message' => 'Invalid authorization'
                ]
            ];
            return $this->getResponse($request, $status, $payload);
        } catch (ValidationException $e) {
            $status = 400;
            $payload = [
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'invalidParams' => $e->getErrors(),
                ]
            ];

            return $this->getResponse($request, $status, $payload);
        } catch (HttpNotFoundException $e) {
            $status = 404;
            $payload = [
                'error' => [
                    'code' => 404,
                    'message' => $e->getMessage(),
                ]
            ];

            return $this->getResponse($request, $status, $payload, $e);
        } catch (HttpMethodNotAllowedException $e) {
            $status = 405;
            $payload = [
                'error' => [
                    'code' => 405,
                    'message' => $e->getMessage(),
                ]
            ];

            return $this->getResponse($request, $status, $payload, $e);
        } catch (Throwable $e) {
            $status = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
            $payload = [
                'error' => [
                    'code' => $status,
                    'message' => 'Unexpected Error',
                ]
            ];
            $this->logger->error($e->__toString());

            return $this->getResponse($request, $status, $payload, $e);
        }
    }

    private function getResponse(RequestInterface $request, int $status, array $payload = [], ?Throwable $e = null): ResponseInterface
    {
        $map = [400 => '4xx', 401 => '4xx', 404 => '4xx', 405 => '4xx',
                500 => '5xx', 502 => '5xx'];
        $templateStatus = array_key_exists($status, $map) ? $map[$status] : substr(strval($status), 0, 1) . 'xx';
        $template = sprintf("errors/%s.html.twig", $templateStatus);

        return $this->htmlResponder->getResponse($status, $template, $payload, $e);
    }
}
