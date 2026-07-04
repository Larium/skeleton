<?php

namespace Larium\Ui\SharedKernel\Service\RequestParser;

use Psr\Http\Message\ServerRequestInterface;

interface BodyParserInterface
{
    public function supports(ServerRequestInterface $request): bool;

    public function parse(string $body): array;
}
