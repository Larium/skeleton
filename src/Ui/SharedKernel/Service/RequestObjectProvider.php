<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Service;

use AutoMapperPlus\DataType;
use AutoMapperPlus\AutoMapperInterface;
use Larium\Ui\SharedKernel\Service\Validation\JsonIntegrityValidation;
use Larium\Ui\SharedKernel\Service\Validation\ValidationService;
use Psr\Http\Message\ServerRequestInterface;

class RequestObjectProvider
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly ValidationService $validation,
    ) {

    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function provide(ServerRequestInterface $request, string $className, array $validationContext): object
    {
        $body = $this->extractBodyData($request);
        $data = array_merge($request->getQueryParams() ?? [], $body);

        $this->ensureArrayMapping($className);
        $dto = $this->mapper->map($data, $className);
        $this->validation->validate($dto, $validationContext);

        return $dto;
    }

    private function extractBodyData(ServerRequestInterface $request): array
    {
        $parsed = $request->getParsedBody();
        if (is_array($parsed)) {
            return $parsed;
        }

        return $this->isJson($request)
            ? (new JsonIntegrityValidation())->validate((string) $request->getBody())
            : [];
    }

    private function ensureArrayMapping(string $className): void
    {
        $config = $this->mapper->getConfiguration();
        if (!$config->hasMappingFor(DataType::ARRAY, $className)) {
            $config->registerMapping(DataType::ARRAY, $className);
        }
    }

    private function isJson(ServerRequestInterface $request): bool
    {
        return str_contains(strtolower($request->getHeaderLine('Content-Type')), 'application/json');
    }
}
