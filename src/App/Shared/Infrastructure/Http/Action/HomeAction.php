<?php

declare(strict_types=1);

namespace Larium\App\Shared\Infrastructure\Http\Action;

use Larium\Framework\Http\Action;
use Larium\Framework\Http\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class HomeAction implements Action
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        return (new ResponseFactory)->createResponse();
    }
}
