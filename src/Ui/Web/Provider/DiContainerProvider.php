<?php

declare(strict_types=1);

namespace Larium\Ui\Web\Provider;

use Dotenv\Dotenv;
use Monolog\Level;
use Monolog\Logger;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use FastRoute\RouteCollector;
use Monolog\Handler\StreamHandler;
use Larium\Bridge\Template\Template;
use Larium\Ui\Web\Provider\RouterProvider;
use Larium\Ui\Web\Provider\ContainerLocator;
use Larium\Ui\Web\Middleware\FirewallMiddleware;
use Larium\Ui\SharedKernel\Authentication\Firewall;
use Larium\Ui\SharedKernel\Authentication\AuthenticatorService;
use Larium\Ui\SharedKernel\Authentication\CredentialCollector;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use Psr\Container\ContainerInterface;
use Larium\Bridge\Template\TwigTemplate;
use Larium\Framework\Contract\Routing\Router;
use Larium\Framework\Provider\ContainerProvider;
use Larium\Framework\Bridge\Routing\FastRouteBridge;

use function FastRoute\simpleDispatcher;

class DiContainerProvider implements ContainerProvider
{
    public function getContainer(): ContainerInterface
    {
        (Dotenv::createImmutable(__DIR__ . '/../../../../'))->load();
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            CommandBus::class => static function (ContainerInterface $c) {
                return new CommandBus([
                    new CommandHandlerMiddleware(
                        new ClassNameExtractor(),
                        new ContainerLocator($c),
                        new HandleInflector()
                    )
                ]);
            },
            Router::class => function () {
                $dispatcher = simpleDispatcher(function (RouteCollector $c) {
                    $routerProvider = new RouterProvider();
                    $routerProvider->register($c);
                });

                return new FastRouteBridge($dispatcher);
            },
            Template::class => function () {
                $template = new TwigTemplate(__DIR__ . '/../templates');
                $template->disableCache();

                return $template;
            },
            LoggerInterface::class => function () {
                $log = new Logger(sprintf('%s-%s', $_ENV['APP_NAME'], $_ENV['APP_ENV']));
                $level = Level::Info;
                if ($_ENV['APP_ENV'] === 'development') {
                    $level = LEvel::Debug;
                }
                $log->pushHandler(new StreamHandler('php://stdout', $level));

                return $log;
            },
            // Authentication services
            AuthenticatorService::class => function () {
                // TODO: Implement actual authenticator service
                // This is a placeholder - you'll need to implement based on your auth strategy
                throw new \RuntimeException('AuthenticatorService not implemented');
            },
            CredentialCollector::class => function () {
                // TODO: Implement actual credential collector
                // This is a placeholder - you'll need to implement based on your auth strategy
                throw new \RuntimeException('CredentialCollector not implemented');
            },
            Firewall::class => function ($c) {
                return new Firewall($c, [
                    '/^\/admin/' => 'adminAuthentication',
                    '/^\/dashboard/' => 'userAuthentication',
                    // Add more route patterns as needed
                ]);
            },
            FirewallMiddleware::class => function ($c) {
                return new FirewallMiddleware($c->get(Firewall::class));
            }
        ]);

        return $builder->build();
    }
}
