<?php

declare(strict_types=1);

namespace Larium\Ui\Http\Action;

use Larium\Ui\Http\Responder\HtmlResponder;
use Larium\Framework\Http\Action;
use Larium\Framework\Http\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class HomeAction implements Action
{
    private HtmlResponder $responder;

    public function __construct(HtmlResponder $responder)
    {
        $this->responder = $responder;
    }
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responder->getResponse(200, 'home/index.html.twig');
    }
}
