<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Action;

use Larium\Framework\Http\Action;
use Psr\Http\Message\ResponseInterface;
use Larium\Ui\Api\Responder\JsonResponder;
use Psr\Http\Message\ServerRequestInterface;

class HomeAction implements Action
{
    public function __construct(
        private readonly JsonResponder $responder
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $payload = [
            'message' => 'Welcome to the API',
            'version' => '1.0.0'
        ];

        return $this->responder->getResponse($payload, 200);
    }
}

