<?php

declare(strict_types=1);

use Larium\Framework\Framework;
use Laminas\Diactoros\ServerRequestFactory;
use Larium\Ui\Web\Provider\DiContainerProvider;
use Larium\Ui\Web\Middleware\ExceptionMiddleware;
use Larium\Framework\Middleware\RoutingMiddleware;
use Larium\Ui\Web\Middleware\SessionMiddlewareFactory;
use Larium\Framework\Middleware\ActionResolverMiddleware;
use Larium\Ui\SharedKernel\Middleware\FirewallMiddleware;

require_once __DIR__ . '/../../vendor/autoload.php';

(function () {
    $containerProvider = new DiContainerProvider();
    $container = $containerProvider->getContainer();

    $f = new Framework($container);

    $f->pipe(ExceptionMiddleware::class, 4);
    $f->pipe((new SessionMiddlewareFactory())->create($_ENV['SYMMETRIC_KEY'] ?? '', 43200), 3);
    $f->pipe(FirewallMiddleware::class, 2);
    $f->pipe(RoutingMiddleware::class, 1);
    $f->pipe(ActionResolverMiddleware::class, 0);

    $f->run(ServerRequestFactory::fromGlobals());
})();
