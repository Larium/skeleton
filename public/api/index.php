<?php

declare(strict_types=1);

use Laminas\Diactoros\ServerRequestFactory;
use Larium\Ui\Api\Middleware\ExceptionMiddleware;
use Larium\Ui\SharedKernel\Middleware\FirewallMiddleware;
use Larium\Ui\Api\Provider\DiContainerProvider;
use Larium\Framework\Framework;
use Larium\Framework\Middleware\ActionResolverMiddleware;
use Larium\Framework\Middleware\RoutingMiddleware;

require_once __DIR__ . '/../../vendor/autoload.php';

(function () {
    $containerProvider = new DiContainerProvider();
    $container = $containerProvider->getContainer();

    $f = new Framework($container);

    $f->pipe(ExceptionMiddleware::class, 4);
    $f->pipe(FirewallMiddleware::class, 3);
    $f->pipe(RoutingMiddleware::class, 1);
    $f->pipe(ActionResolverMiddleware::class, 0);

    $f->run(ServerRequestFactory::fromGlobals());
})();

