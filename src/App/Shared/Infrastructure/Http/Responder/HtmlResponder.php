<?php

declare(strict_types=1);

namespace Larium\App\Shared\Infrastructure\Http\Responder;

use Throwable;
use Larium\Bridge\Template\Template;
use Psr\Http\Message\ResponseInterface;
use Larium\Framework\Http\ResponseFactory;

class HtmlResponder
{
    /**
     * @var Template
     */
    private $engine;

    public function __construct(Template $engine)
    {
        $this->engine = $engine;
    }

    public function getResponse(
        int $status,
        string $template,
        array $payload = [],
        Throwable $e = null
    ): ResponseInterface {
        $payload['e'] = $e;
        $response = (new ResponseFactory())->createResponse($status);
        $response = $response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($this->engine->render($template, $payload));
        $response->getBody()->rewind();

        return $response;
    }
}

