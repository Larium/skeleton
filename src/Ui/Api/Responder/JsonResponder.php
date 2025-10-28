<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Responder;

use const JSON_ERROR_NONE;

use stdClass;
use JsonSerializable;
use InvalidArgumentException;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Larium\Framework\Http\ResponseFactory;

use function json_encode;
use function json_last_error;
use function json_last_error_msg;

class JsonResponder
{
    public function getResponse(
        array | JsonSerializable | stdClass $payload,
        int $status = 200,
        array $headers = []
    ): ResponseInterface {
        $response = (new ResponseFactory())->createResponse($status);
        $body = json_encode($payload);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(json_last_error_msg(), json_last_error());
        }
        $stream = (new StreamFactory())->createStream($body);

        $response = $response->withBody($stream)
            ->withHeader('Content-Type', 'application/json');
        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
