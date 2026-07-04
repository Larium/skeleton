<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Service;

use AutoMapperPlus\DataType;
use AutoMapperPlus\AutoMapperInterface;
use Psr\Http\Message\ServerRequestInterface;
use Larium\Ui\SharedKernel\Service\Validation\ValidationService;
use Larium\Ui\SharedKernel\Service\RequestParser\BodyParserInterface;
use Larium\Ui\SharedKernel\Service\RequestParser\UnsupportedContentTypeException;

class RequestObjectProvider
{
    public function __construct(
        private readonly AutoMapperInterface $mapper,
        private readonly ValidationService $validation,
        private readonly BodyParserInterface $bodyParser,
    ) {

    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T
     */
    public function provide(ServerRequestInterface $request, string $className, array $validationContext): object
    {
        if (!$this->bodyParser->supports($request)) {
            throw new UnsupportedContentTypeException('Content type is not supported');
        }

        $body = $this->bodyParser->parse($request->getBody()->__toString());
        $data = array_merge($request->getQueryParams() ?? [], $body);

        $this->ensureArrayMapping($className);
        $dto = $this->mapper->map($data, $className);
        $this->validation->validate($dto, $validationContext);

        return $dto;
    }

    private function ensureArrayMapping(string $className): void
    {
        $config = $this->mapper->getConfiguration();
        if (!$config->hasMappingFor(DataType::ARRAY, $className)) {
            $config->registerMapping(DataType::ARRAY, $className);
        }
    }
}
