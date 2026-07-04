<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Service\RequestParser;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use Larium\Ui\SharedKernel\Service\Validation\JsonIntegrityValidation;

class JsonBodyParser implements BodyParserInterface
{
    #[Override]
    public function supports(ServerRequestInterface $request): bool
    {
        return str_contains(strtolower($request->getHeaderLine('Content-Type')), 'json');
    }

    #[Override]
    public function parse(string $body): array
    {
        return (new JsonIntegrityValidation())->validate($body);
    }
}
