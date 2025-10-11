<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Authentication;

use Psr\Container\ContainerInterface;

class Firewall
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $config
    ) {
    }

    public function apply(string $path): ?AuthenticatorService
    {
        $patterns = array_keys($this->config);
        $serviceName = null;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $path) === 0) {
                continue;
            }
            $serviceName = $this->config[$pattern];
            break;
        }

        return $serviceName ? $this->container->get($serviceName) : null;
    }
}
