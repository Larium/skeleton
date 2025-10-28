<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Larium\Ui\SharedKernel\Authentication\Firewall;

class FirewallMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Firewall $firewall
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $service = $this->firewall->apply($request->getUri()->getPath());

        if ($service == null) {
            return $handler->handle($request);
        }

        $result = $service->__invoke($request);
        $request = $request->withAttribute('user', $result);

        return $handler->handle($request);
    }
}

