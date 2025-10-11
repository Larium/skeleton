<?php

declare(strict_types=1);

namespace Larium\Ui\SharedKernel\Service;

use Psr\Container\ContainerInterface;
use League\Tactician\Handler\Locator\HandlerLocator;

class ContainerLocator implements HandlerLocator
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getHandlerForCommand($commandName)
    {
        $handlerName = str_replace(['Command', 'Query'], [null, null], $commandName);

        return $this->container->get(sprintf("%sHandler", $handlerName));
    }
}
