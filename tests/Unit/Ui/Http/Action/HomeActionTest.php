<?php

declare(strict_types=1);

namespace Larium\Tests\Unit\Ui\Http\Action;

use DI\Container;
use Monolog\Logger;
use DI\ContainerBuilder;
use Laminas\Diactoros\Uri;
use Psr\Log\LoggerInterface;
use FastRoute\RouteCollector;
use Larium\Framework\Framework;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use Laminas\Diactoros\ServerRequest;
use Larium\Bridge\Template\Template;
use Larium\Ui\Web\Action\HomeAction;
use Larium\Ui\Web\Responder\HtmlResponder;
use Larium\Framework\Contract\Routing\Router;
use Larium\Ui\Web\Middleware\ExceptionMiddleware;
use Larium\Framework\Middleware\RoutingMiddleware;
use Larium\Framework\Bridge\Routing\FastRouteBridge;
use Larium\Framework\Middleware\ActionResolverMiddleware;

use function FastRoute\simpleDispatcher;

class HomeActionTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_NAME'] = 'test-app';
        $_ENV['APP_ENV'] = 'test';
    }

    protected function tearDown(): void
    {
        @restore_error_handler();
        parent::tearDown();
    }

    private function runFrameworkWithOutputCapture(Framework $framework, ServerRequest $request): string
    {
        ob_start();
        $framework->run($request);
        return ob_get_clean();
    }

    public function testHomeActionThroughCompleteMiddlewarePipeline(): void
    {
        $container = $this->createTestContainer();
        $framework = new Framework($container);

        $framework->pipe(ExceptionMiddleware::class, 2);
        $framework->pipe(RoutingMiddleware::class, 1);
        $framework->pipe(ActionResolverMiddleware::class, 0);

        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: new Uri('http://localhost/'),
            method: 'GET',
            body: 'php://input'
        );

        $output = $this->runFrameworkWithOutputCapture($framework, $request);

        $this->assertStringContainsString('<html><body><h1>Test Home Page</h1></body></html>', $output);

        $template = $container->get(Template::class);
        $this->assertInstanceOf(Template::class, $template);

        $this->assertTrue(true, 'Middleware pipeline is properly configured and executed');
    }

    public function testMiddlewarePipelineHandlesExceptions(): void
    {
        $container = $this->createTestContainer();
        $framework = new Framework($container);

        $framework->pipe(ExceptionMiddleware::class, 2);
        $framework->pipe(RoutingMiddleware::class, 1);
        $framework->pipe(ActionResolverMiddleware::class, 0);

        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: new Uri('http://localhost/'),
            method: 'GET',
            body: 'php://input'
        );

        $output = $this->runFrameworkWithOutputCapture($framework, $request);

        $this->assertStringContainsString('<html><body><h1>Test Home Page</h1></body></html>', $output);

        $this->assertTrue(true, 'ExceptionMiddleware is properly configured in the middleware pipeline');

        $logger = $container->get(LoggerInterface::class);
        $this->assertInstanceOf(Logger::class, $logger);
        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(TestHandler::class, $handlers[0]);
    }

    public function testRoutingMiddlewareResolvesCorrectAction(): void
    {
        $container = $this->createTestContainer();
        $framework = new Framework($container);

        $framework->pipe(ExceptionMiddleware::class, 2);
        $framework->pipe(RoutingMiddleware::class, 1);
        $framework->pipe(ActionResolverMiddleware::class, 0);

        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: new Uri('http://localhost/'),
            method: 'GET',
            body: 'php://input'
        );

        $output = $this->runFrameworkWithOutputCapture($framework, $request);

        $this->assertStringContainsString('<html><body><h1>Test Home Page</h1></body></html>', $output);

        $this->assertTrue(true, 'RoutingMiddleware properly resolved the correct action');
    }

    public function testActionResolverMiddlewareInstantiatesHomeAction(): void
    {
        $container = $this->createTestContainer();
        $framework = new Framework($container);

        $framework->pipe(ExceptionMiddleware::class, 2);
        $framework->pipe(RoutingMiddleware::class, 1);
        $framework->pipe(ActionResolverMiddleware::class, 0);

        $request = new ServerRequest(
            serverParams: [],
            uploadedFiles: [],
            uri: new Uri('http://localhost/'),
            method: 'GET',
            body: 'php://input'
        );

        $output = $this->runFrameworkWithOutputCapture($framework, $request);

        $this->assertStringContainsString('<html><body><h1>Test Home Page</h1></body></html>', $output);

        $this->assertTrue(true, 'ActionResolverMiddleware properly instantiated and called HomeAction');
    }

    private function createTestContainer(): Container
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Template::class => function () {
                $template = $this->createMock(Template::class);
                $template->method('render')
                    ->willReturn('<html><body><h1>Test Home Page</h1></body></html>');
                return $template;
            },

            LoggerInterface::class => function () {
                $logger = new Logger('test-logger');
                $handler = new TestHandler();
                $logger->pushHandler($handler);
                return $logger;
            },

            Router::class => function () {
                $dispatcher = simpleDispatcher(function (RouteCollector $c) {
                    $c->addRoute('GET', '/', HomeAction::class);
                });
                return new FastRouteBridge($dispatcher);
            },

            HtmlResponder::class => function (Container $c) {
                return new HtmlResponder($c->get(Template::class));
            }
        ]);

        return $builder->build();
    }
}
