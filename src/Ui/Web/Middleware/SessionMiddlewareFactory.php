<?php

declare(strict_types=1);

namespace Larium\Ui\Web\Middleware;

use Lcobucci\Clock\SystemClock;
use Dflydev\FigCookies\SetCookie;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Dflydev\FigCookies\Modifier\SameSite;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Http\Configuration as StoragelessConfiguration;

class SessionMiddlewareFactory
{
    public const DEFAULT_COOKIE = 'slsession';

    public function create(string $key, int $expirationTime): SessionMiddleware
    {
        return new SessionMiddleware(
            StoragelessConfiguration::fromJwtConfiguration(
                JwtConfiguration::forSymmetricSigner(
                    new Sha256(),
                    InMemory::plainText($key)
                )
            )
                ->withClock(SystemClock::fromSystemTimezone())
                ->withIdleTimeout($expirationTime)
                ->withRefreshTime(60)
                ->withCookie($this->createCookie())
        );
    }

    private function createCookie(): SetCookie
    {
        return SetCookie::create(self::DEFAULT_COOKIE)
            ->withSameSite(SameSite::lax())
            ->withPath('/')
            ->withSecure(!($_ENV['APP_ENV'] === 'development'))
            ->withHttpOnly(true);
    }
}
