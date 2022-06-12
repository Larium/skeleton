<?php

declare(strict_types=1);

use Laminas\Diactoros\ServerRequestFactory;
use Larium\App\Shared\Infrastructure\Http\Middleware\ExceptionMiddleware;
use Larium\App\Shared\Infrastructure\Provider\DiContainerProvider;
use Larium\Framework\Framework;
use Larium\Framework\Middleware\ActionResolverMiddleware;
use Larium\Framework\Middleware\RoutingMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

(function () {
    $containerProvider = new DiContainerProvider();
    $container = $containerProvider->getContainer();

    $f = new Framework($container);

    $f->pipe(ExceptionMiddleware::class, 2);
    $f->pipe(RoutingMiddleware::class, 1);
    $f->pipe(ActionResolverMiddleware::class, 0);

    $f->run(ServerRequestFactory::fromGlobals());
})();
