<?php

declare(strict_types=1);

namespace Larium\App\Shared\Infrastructure\Provider;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use FastRoute\RouteCollector;
use Larium\App\Shared\Infrastructure\Http\Action\HomeAction;
use Larium\Bridge\Template\Template;

use function FastRoute\simpleDispatcher;
use Larium\Bridge\Template\TwigTemplate;
use Larium\Framework\Bridge\Routing\FastRouteBridge;
use Larium\Framework\Contract\Routing\Router;
use Larium\Framework\Provider\ContainerProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class DiContainerProvider implements ContainerProvider
{
    public function getContainer(): ContainerInterface
    {
        (Dotenv::createImmutable(__DIR__ . '/../../../../../'))->load();
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Router::class => function () {
                $dispatcher = simpleDispatcher(function (RouteCollector $c) {
                    $c->addRoute('GET', '/', HomeAction::class);
                });

                return new FastRouteBridge($dispatcher);
            },
            Template::class => function () {
                $template = new TwigTemplate(__DIR__ . '/../Http/templates');
                $template->disableCache();

                return $template;
            },
            LoggerInterface::class => function () {
                $log = new Logger(sprintf('%s-%s', $_ENV['APP_NAME'], $_ENV['APP_ENV']));
                $level = Logger::INFO;
                if ($_ENV['APP_ENV'] === 'development') {
                    $level = Logger::DEBUG;
                }
                $log->pushHandler(new StreamHandler('php://stdout', $level));

                return $log;
            }
        ]);

        return $builder->build();
    }
}
