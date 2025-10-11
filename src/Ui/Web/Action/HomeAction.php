<?php

declare(strict_types=1);

namespace Larium\Ui\Web\Action;

use Larium\Framework\Http\Action;
use Psr\Http\Message\ResponseInterface;
use Larium\Ui\Web\Responder\HtmlResponder;
use Psr\Http\Message\ServerRequestInterface;

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
